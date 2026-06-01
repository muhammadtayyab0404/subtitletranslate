
# With mini model of Qwen
import re
import time
import torch
import fasttext
import stanza

from simalign import SentenceAligner
from pathlib import Path
from functools import lru_cache
from dataclasses import dataclass

from transformers import (
    AutoConfig,
    AutoModelForSeq2SeqLM,
    AutoTokenizer,
    AutoModelForCausalLM,
)
from transformers.models.nllb.tokenization_nllb import FAIRSEQ_LANGUAGE_CODES

try:
    from transformers import BitsAndBytesConfig
except Exception:
    BitsAndBytesConfig = None


BASE_DIR = Path(__file__).resolve().parent


# =========================
# Config
# =========================
MODEL_NAME = "facebook/nllb-200-distilled-600M"
EXPLAINER_MODEL_NAME = "Qwen/Qwen2.5-1.5B-Instruct"

# Faster + more stable than sampling; keeps same output fields.
AI_DO_SAMPLE = False
AI_TEMPERATURE = 0.7
AI_TOP_P = 0.9
AI_TOP_K = 40

# Optional optimizations
USE_8BIT_EXPLAINER_IF_POSSIBLE = True
LOW_CPU_MEM_USAGE = True

device = "cuda" if torch.cuda.is_available() else "cpu"
explainer_device = "cuda" if torch.cuda.is_available() else "cpu"

# =========================
# Lazy-loaded globals
# =========================
tokenizer = None
model = None

explainer_tokenizer = None
explainer_model = None

nlp_en = None
detector = None
aligner = None

# =========================
# Language metadata (cheap, keep eager)
# =========================
ALL_NLLB_LANGS = sorted(FAIRSEQ_LANGUAGE_CODES)
ALL_NLLB_LANGS_SET = set(ALL_NLLB_LANGS)
print(f"Loaded {len(ALL_NLLB_LANGS)} NLLB language tags.")

CANONICAL_TAG_LOOKUP = {tag.lower(): tag for tag in ALL_NLLB_LANGS}

BASE_TO_VARIANTS = {}
for tag in ALL_NLLB_LANGS:
    base = tag.split("_")[0].lower()
    BASE_TO_VARIANTS.setdefault(base, []).append(tag)

AMBIGUOUS_DEFAULTS = {
    "ace": "ace_Latn",
    "bjn": "bjn_Latn",
    "kas": "kas_Arab",
    "knc": "knc_Latn",
    "taq": "taq_Latn",
    "zho": "zho_Hans",
}

BASE_TO_DEFAULT = {}
for base, variants in BASE_TO_VARIANTS.items():
    if len(variants) == 1:
        BASE_TO_DEFAULT[base] = variants[0]
    else:
        BASE_TO_DEFAULT[base] = AMBIGUOUS_DEFAULTS.get(base)

LANGUAGE_ALIASES = {
    # short codes
    "af": "afr_Latn", "am": "amh_Ethi", "ar": "arb_Arab", "az": "azj_Latn",
    "be": "bel_Cyrl", "bg": "bul_Cyrl", "bn": "ben_Beng", "bs": "bos_Latn",
    "ca": "cat_Latn", "cs": "ces_Latn", "cy": "cym_Latn", "da": "dan_Latn",
    "de": "deu_Latn", "el": "ell_Grek", "en": "eng_Latn", "eo": "epo_Latn",
    "es": "spa_Latn", "et": "est_Latn", "eu": "eus_Latn", "fa": "pes_Arab",
    "fi": "fin_Latn", "fr": "fra_Latn", "ga": "gle_Latn", "gl": "glg_Latn",
    "gu": "guj_Gujr", "he": "heb_Hebr", "hi": "hin_Deva", "hr": "hrv_Latn",
    "hu": "hun_Latn", "hy": "hye_Armn", "id": "ind_Latn", "is": "isl_Latn",
    "it": "ita_Latn", "ja": "jpn_Jpan", "jv": "jav_Latn", "ka": "kat_Geor",
    "kk": "kaz_Cyrl", "km": "khm_Khmr", "ko": "kor_Hang", "ky": "kir_Cyrl",
    "lo": "lao_Laoo", "lt": "lit_Latn", "lv": "lvs_Latn", "ml": "mal_Mlym",
    "mr": "mar_Deva", "ms": "zsm_Latn", "mt": "mlt_Latn", "my": "mya_Mymr",
    "ne": "npi_Deva", "nl": "nld_Latn", "nn": "nno_Latn", "no": "nob_Latn",
    "or": "ory_Orya", "pa": "pan_Guru", "pl": "pol_Latn", "ps": "pbt_Arab",
    "pt": "por_Latn", "ro": "ron_Latn", "ru": "rus_Cyrl", "sd": "snd_Arab",
    "si": "sin_Sinh", "sk": "slk_Latn", "sl": "slv_Latn", "so": "som_Latn",
    "sq": "als_Latn", "sr": "srp_Cyrl", "sv": "swe_Latn", "sw": "swh_Latn",
    "ta": "tam_Taml", "te": "tel_Telu", "tg": "tgk_Cyrl", "th": "tha_Thai",
    "tk": "tuk_Latn", "tl": "tgl_Latn", "tr": "tur_Latn", "ug": "uig_Arab",
    "uk": "ukr_Cyrl", "ur": "urd_Arab", "uz": "uzn_Latn", "vi": "vie_Latn",
    "xh": "xho_Latn", "yo": "yor_Latn", "zh": "zho_Hans", "zu": "zul_Latn",

    # names
    "english": "eng_Latn",
    "urdu": "urd_Arab",
    "german": "deu_Latn",
    "french": "fra_Latn",
    "spanish": "spa_Latn",
    "arabic": "arb_Arab",
    "hindi": "hin_Deva",
    "japanese": "jpn_Jpan",
    "korean": "kor_Hang",
    "turkish": "tur_Latn",
    "vietnamese": "vie_Latn",
    "persian": "pes_Arab",
    "farsi": "pes_Arab",
    "chinese": "zho_Hans",
    "traditional_chinese": "zho_Hant",
    "simplified_chinese": "zho_Hans",
    "cantonese": "yue_Hant",
    "albanian": "als_Latn",
    "malay": "zsm_Latn",
    "indonesian": "ind_Latn",
    "swahili": "swh_Latn",
}

for tag in ALL_NLLB_LANGS:
    LANGUAGE_ALIASES.setdefault(tag.lower(), tag)

for base, default_tag in BASE_TO_DEFAULT.items():
    if default_tag:
        LANGUAGE_ALIASES.setdefault(base, default_tag)

# =========================
# Lazy loaders
# =========================
def _get_translation_components():
    global tokenizer, model

    if tokenizer is not None and model is not None:
        return tokenizer, model

    print("Loading translation model...")

    translation_config = AutoConfig.from_pretrained(MODEL_NAME)
    translation_config.tie_word_embeddings = False

    tokenizer = AutoTokenizer.from_pretrained(MODEL_NAME)

    model_kwargs = {
        "config": translation_config,
        "low_cpu_mem_usage": LOW_CPU_MEM_USAGE,
    }

    if device == "cuda":
        model_kwargs["torch_dtype"] = torch.float16

    model = AutoModelForSeq2SeqLM.from_pretrained(MODEL_NAME, **model_kwargs)

    if device == "cuda":
        model = model.to(device)
    else:
        model = model.to(device)

    model.eval()
    return tokenizer, model


def _get_explainer_components():
    global explainer_tokenizer, explainer_model

    if explainer_tokenizer is not None and explainer_model is not None:
        return explainer_tokenizer, explainer_model

    print("Loading explanation model...")

    explainer_tokenizer = AutoTokenizer.from_pretrained(EXPLAINER_MODEL_NAME, use_fast=True)
    if explainer_tokenizer.pad_token is None:
        explainer_tokenizer.pad_token = explainer_tokenizer.eos_token

    loaded_in_8bit = False

    if explainer_device == "cuda":
        if USE_8BIT_EXPLAINER_IF_POSSIBLE and BitsAndBytesConfig is not None:
            try:
                quant_config = BitsAndBytesConfig(load_in_8bit=True)
                explainer_model = AutoModelForCausalLM.from_pretrained(
                    EXPLAINER_MODEL_NAME,
                    quantization_config=quant_config,
                    device_map="auto",
                    low_cpu_mem_usage=LOW_CPU_MEM_USAGE,
                )
                loaded_in_8bit = True
            except Exception:
                loaded_in_8bit = False

        if not loaded_in_8bit:
            explainer_model = AutoModelForCausalLM.from_pretrained(
                EXPLAINER_MODEL_NAME,
                torch_dtype=torch.float16,
                low_cpu_mem_usage=LOW_CPU_MEM_USAGE,
            )
            explainer_model = explainer_model.to(explainer_device)
    else:
        explainer_model = AutoModelForCausalLM.from_pretrained(
            EXPLAINER_MODEL_NAME,
            low_cpu_mem_usage=LOW_CPU_MEM_USAGE,
        )
        explainer_model = explainer_model.to(explainer_device)

    explainer_model.eval()
    return explainer_tokenizer, explainer_model


def _get_nlp_en():
    global nlp_en

    if nlp_en is not None:
        return nlp_en

    print("Loading English grammar parser...")
    try:
        nlp_en = stanza.Pipeline(
            lang="en",
            processors="tokenize,pos,lemma,depparse",
            use_gpu=torch.cuda.is_available(),
            verbose=False,
        )
    except Exception:
        print("Downloading Stanza English model...")
        stanza.download("en", verbose=False)
        nlp_en = stanza.Pipeline(
            lang="en",
            processors="tokenize,pos,lemma,depparse",
            use_gpu=torch.cuda.is_available(),
            verbose=False,
        )

    return nlp_en


def _get_detector():
    global detector

    if detector is not None:
        return detector

    print("Loading language detector...")
    fasttext_model_path = BASE_DIR / "lid.176.bin"

    if not fasttext_model_path.exists():
        raise FileNotFoundError(f"fastText model not found: {fasttext_model_path}")

    detector = fasttext.load_model(str(fasttext_model_path))
    return detector


def _get_aligner():
    global aligner

    if aligner is not None:
        return aligner

    print("Loading contextual word aligner...")
    aligner = SentenceAligner(model="bert", token_type="bpe", matching_methods="mai")
    return aligner


# =========================
# Script helpers for ambiguous variants
# =========================
def _has_char_in_ranges(text, ranges):
    for ch in text:
        cp = ord(ch)
        for start, end in ranges:
            if start <= cp <= end:
                return True
    return False


ARABIC_RANGES = [
    (0x0600, 0x06FF), (0x0750, 0x077F), (0x08A0, 0x08FF),
    (0xFB50, 0xFDFF), (0xFE70, 0xFEFF),
]
DEVANAGARI_RANGES = [(0x0900, 0x097F)]
TIFINAGH_RANGES = [(0x2D30, 0x2D7F)]


def choose_ambiguous_variant(base, text):
    if base == "kas":
        return "kas_Deva" if _has_char_in_ranges(text, DEVANAGARI_RANGES) else "kas_Arab"
    if base in {"ace", "bjn", "knc"}:
        return f"{base}_Arab" if _has_char_in_ranges(text, ARABIC_RANGES) else f"{base}_Latn"
    if base == "taq":
        return "taq_Tfng" if _has_char_in_ranges(text, TIFINAGH_RANGES) else "taq_Latn"
    if base == "zho":
        return "zho_Hans"
    return BASE_TO_DEFAULT.get(base)


# =========================
# Language resolution
# =========================
def normalize_lang_key(code: str) -> str:
    return code.strip().lower().replace("-", "_").replace(" ", "_")


def resolve_lang_code(code, text=""):
    if not isinstance(code, str) or not code.strip():
        raise ValueError("Language code must be a non-empty string.")

    if code in ALL_NLLB_LANGS_SET:
        return code

    key = normalize_lang_key(code)

    if key in CANONICAL_TAG_LOOKUP:
        return CANONICAL_TAG_LOOKUP[key]

    if key in BASE_TO_VARIANTS:
        variants = BASE_TO_VARIANTS[key]
        if len(variants) == 1:
            return variants[0]
        chosen = choose_ambiguous_variant(key, text)
        if chosen:
            return chosen

    if key in LANGUAGE_ALIASES:
        alias_value = LANGUAGE_ALIASES[key]
        if alias_value in BASE_TO_VARIANTS:
            variants = BASE_TO_VARIANTS[alias_value]
            if len(variants) == 1:
                return variants[0]
            chosen = choose_ambiguous_variant(alias_value, text)
            if chosen:
                return chosen
        return alias_value

    raise ValueError(f"Unsupported or unmapped language code: {code}")


# =========================
# Cleanup / text helpers
# =========================
def _cleanup_generated_text(text, keep_newlines=False):
    text = re.sub(r"https?://\S+|www\.\S+", "", text)
    text = text.replace("&quot;", '"').replace("&amp;", "&")

    if keep_newlines:
        lines = [re.sub(r"\s+", " ", line).strip() for line in text.splitlines()]
        lines = [line for line in lines if line]
        text = "\n".join(lines)
    else:
        text = re.sub(r"\s+", " ", text).strip()

    return text.strip(" -")


def _looks_garbled(text):
    if not text:
        return True

    t = text.lower()
    words = t.split()

    if any(x in t for x in ["http", "www", "&quot;", "&amp;"]):
        return True

    if re.search(r"(for){4,}", t):
        return True

    if re.search(r"([a-z]{2,})\1{3,}", t):
        return True

    if len(words) >= 6:
        weird = sum(1 for w in words if re.search(r"[^a-zA-Z]", w))
        if weird / len(words) > 0.6:
            return True

    return False


# =========================
# Detection
# =========================
@lru_cache(maxsize=4096)
def _detect_language_cached(clean_text):
    ft = _get_detector()
    labels, scores = ft.predict(clean_text)

    lang = labels[0].replace("__label__", "")

    try:
        confidence_value = scores[0]
    except Exception:
        confidence_value = scores

    confidence = float(confidence_value)
    return lang, confidence


def detect_language(text):
    clean_text = " ".join(text.split())
    lang, confidence = _detect_language_cached(clean_text)
    print(f"Detected language: {lang} (confidence: {confidence:.2f})")
    return lang, confidence


# =========================
# Internal translation core
# =========================
@lru_cache(maxsize=4096)
def _generate_translation_cached(text, source_tag, target_tag, max_new_tokens):
    tok, mdl = _get_translation_components()
    tok.src_lang = source_tag
    inputs = tok(text, return_tensors="pt").to(device)
    forced_bos_token_id = tok.convert_tokens_to_ids(target_tag)

    with torch.inference_mode():
        generated_tokens = mdl.generate(
            **inputs,
            forced_bos_token_id=forced_bos_token_id,
            max_new_tokens=int(max_new_tokens),
            num_beams=1,
            do_sample=False,
            use_cache=True,
        )
        result = tok.batch_decode(generated_tokens, skip_special_tokens=True)[0]

    return result


def _generate_translation(text, source_tag, target_tag, max_new_tokens=200):
    return _generate_translation_cached(
        str(text),
        str(source_tag),
        str(target_tag),
        int(max_new_tokens),
    )


# =========================
# Qwen explainer core
# =========================
def _build_qwen_prompt(user_prompt: str):
    tok, _ = _get_explainer_components()

    messages = [
        {
            "role": "system",
            "content": (
                "You are a precise language tutor. "
                "Be accurate, clear, and concise. "
                "Do not add links, code, or random text."
            ),
        },
        {"role": "user", "content": user_prompt},
    ]

    if hasattr(tok, "apply_chat_template"):
        return tok.apply_chat_template(
            messages,
            tokenize=False,
            add_generation_prompt=True,
        )

    return (
        "System: You are a precise language tutor.\n"
        f"User: {user_prompt}\n"
        "Assistant:"
    )


@lru_cache(maxsize=1024)
def _generate_with_explainer_cached(prompt, max_new_tokens, keep_newlines):
    tok, mdl = _get_explainer_components()
    rendered_prompt = _build_qwen_prompt(prompt)
    inputs = tok(rendered_prompt, return_tensors="pt").to(explainer_device)

    gen_kwargs = {
        "max_new_tokens": int(max_new_tokens),
        "do_sample": AI_DO_SAMPLE,
        "repetition_penalty": 1.08,
        "pad_token_id": tok.pad_token_id,
        "eos_token_id": tok.eos_token_id,
        "use_cache": True,
    }

    if AI_DO_SAMPLE:
        gen_kwargs["temperature"] = AI_TEMPERATURE
        gen_kwargs["top_p"] = AI_TOP_P
        gen_kwargs["top_k"] = AI_TOP_K

    with torch.inference_mode():
        outputs = mdl.generate(**inputs, **gen_kwargs)

    new_tokens = outputs[0, inputs["input_ids"].shape[1]:]
    text = tok.decode(new_tokens, skip_special_tokens=True)

    return _cleanup_generated_text(text, keep_newlines=bool(keep_newlines))


def _generate_with_explainer(prompt, max_new_tokens=120, keep_newlines=False):
    return _generate_with_explainer_cached(
        str(prompt),
        int(max_new_tokens),
        bool(keep_newlines),
    )


# =========================
# Tokenization helpers
# =========================
WORD_RE = re.compile(r"\w+|[^\w\s]", re.UNICODE)


@lru_cache(maxsize=8192)
def _split_text_for_meanings_cached(text):
    parts = WORD_RE.findall(text)
    return tuple(p for p in parts if p.strip())


def split_text_for_meanings(text):
    return list(_split_text_for_meanings_cached(str(text)))


def _is_punctuation_only(token):
    return bool(re.fullmatch(r"[^\w\s]+", token, re.UNICODE))


# =========================
# Context-aware meanings
# =========================
@lru_cache(maxsize=2048)
def _word_meanings_cached(text, source_tag, target_tag, translated_text):
    src_tokens = list(_split_text_for_meanings_cached(text))
    tgt_tokens = list(_split_text_for_meanings_cached(translated_text))

    if not src_tokens or not tgt_tokens:
        return tuple()

    try:
        local_aligner = _get_aligner()
        alignments = local_aligner.get_word_aligns(src_tokens, tgt_tokens)
    except Exception:
        return tuple()

    pairs = (
        alignments.get("itermax")
        or alignments.get("mwmf")
        or alignments.get("inter")
        or []
    )

    src_to_tgt = {}
    for s_idx, t_idx in pairs:
        src_to_tgt.setdefault(s_idx, []).append(t_idx)

    results = []
    for s_idx, src_word in enumerate(src_tokens):
        if _is_punctuation_only(src_word):
            continue

        aligned_tgt_idxs = sorted(set(src_to_tgt.get(s_idx, [])))

        if aligned_tgt_idxs:
            aligned_phrase = " ".join(
                tgt_tokens[i]
                for i in aligned_tgt_idxs
                if 0 <= i < len(tgt_tokens)
            ).strip()
            if not aligned_phrase:
                aligned_phrase = "[no alignment]"
        else:
            aligned_phrase = "[no alignment]"

        results.append((src_word, aligned_phrase))

    return tuple(results)


def word_meanings(text, source_lang, target_lang, translated_text=None):
    source_tag = resolve_lang_code(source_lang, text=text)
    target_tag = resolve_lang_code(target_lang)

    if translated_text is None:
        translated_text = _generate_translation(text, source_tag, target_tag)

    return list(
        _word_meanings_cached(
            str(text),
            str(source_tag),
            str(target_tag),
            str(translated_text),
        )
    )


def print_word_meanings(text, source_lang, target_lang, translated_text=None):
    items = word_meanings(text, source_lang, target_lang, translated_text=translated_text)

    if not items:
        print("No contextual word meanings found.")
        return items

    print("Context-aware word meanings:")
    for i, (src, tgt) in enumerate(items, start=1):
        print(f"{i}. {src} -> {tgt}")
    print()
    return items


# =========================
# English grammar parsing helpers (Stanza)
# =========================
@dataclass(frozen=True)
class ParsedWord:
    id: int
    text: str
    head: int
    deprel: str
    lemma: str
    feats: str
    xpos: str


@lru_cache(maxsize=2048)
def _get_en_words_cached(english_text):
    pipeline = _get_nlp_en()
    doc = pipeline(english_text)

    if not doc.sentences:
        return tuple()

    words = []
    for w in doc.sentences[0].words:
        words.append(
            ParsedWord(
                id=int(w.id),
                text=str(w.text),
                head=int(w.head),
                deprel=str(w.deprel or ""),
                lemma=str(w.lemma or ""),
                feats=str(w.feats or ""),
                xpos=str(w.xpos or ""),
            )
        )

    return tuple(words)


def _get_en_words(english_text):
    return list(_get_en_words_cached(str(english_text)))


def _build_children_map(words):
    children = {}
    for w in words:
        children.setdefault(w.head, []).append(w)
    for k in children:
        children[k].sort(key=lambda x: x.id)
    return children


def _collect_subtree_ids(word_id, children):
    ids = []

    def dfs(curr_id):
        ids.append(curr_id)
        for child in children.get(curr_id, []):
            dfs(child.id)

    dfs(word_id)
    return sorted(set(ids))


def _phrase_from_word(target_word, words, children):
    ids = _collect_subtree_ids(target_word.id, children)
    id_to_word = {w.id: w.text for w in words}
    return " ".join(id_to_word[i] for i in ids if i in id_to_word).strip()


def _find_root(words):
    for w in words:
        if w.head == 0:
            return w
    return words[0] if words else None


def _find_main_verb(words, root, children):
    if root is None:
        return None

    for child in children.get(root.id, []):
        if child.deprel == "cop":
            return child

    return root


def _find_subject_phrase(words, children):
    for w in words:
        if w.deprel.startswith("nsubj") or w.deprel == "expl":
            return _phrase_from_word(w, words, children)
    return "Implicit"


def _find_object_phrase(words, children):
    wh_word = words[0].text.lower() if words else ""

    for w in words:
        if w.deprel in {"obj", "iobj"}:
            return _phrase_from_word(w, words, children)

    for w in words:
        if w.deprel in {"obl", "xcomp", "ccomp"}:
            return _phrase_from_word(w, words, children)

    if wh_word == "where":
        return "No explicit object (location is being asked)"
    if wh_word == "why":
        return "No explicit object (reason is being asked)"

    return "No explicit object"


def _infer_tense(words, root, children):
    main_verb = _find_main_verb(words, root, children)
    if main_verb is None:
        return "Unknown"

    feats = main_verb.feats or ""
    lemma = (main_verb.lemma or "").lower()
    xpos = main_verb.xpos or ""

    aux_words = [w for w in words if w.deprel.startswith("aux")]
    aux_lemmas = {(w.lemma or "").lower() for w in aux_words}

    if xpos == "MD" or lemma in {"can", "could", "may", "might", "must", "shall", "should", "will", "would"}:
        return "Modal construction"

    if "Tense=Pres" in feats:
        return "Present"

    if "Tense=Past" in feats:
        return "Past"

    if "VerbForm=Inf" in feats:
        return "Infinitive"

    if "VerbForm=Part" in feats:
        if "be" in aux_lemmas:
            return "Passive / participle construction"
        return "Participle"

    return "Unknown"


def _infer_structure(words):
    if not words:
        return "Unknown"

    first = words[0].text.lower()
    if first in {"who", "what", "where", "when", "why", "how", "which"}:
        return "WH-question"

    return "Declarative or simple sentence"


def _fallback_description_from_parse(english_text):
    words = _get_en_words(english_text)
    if not words:
        return "The speaker is asking for information or expressing a simple idea."

    children = _build_children_map(words)
    subject = _find_subject_phrase(words, children)
    first = words[0].text.lower()

    if first == "where":
        if subject != "Implicit":
            return f"The speaker is asking about the location of {subject}."
        return "The speaker is asking about the location of something."

    if first == "why":
        return "The speaker is asking for a reason or explanation."

    if first in {"who", "what", "when", "how", "which"}:
        return "The speaker is asking a question and wants specific information."

    return "The speaker is expressing a simple idea."


def _default_grammar_notes(words, root, children):
    notes = []
    first = words[0].text.lower() if words else ""
    main_verb = _find_main_verb(words, root, children)

    if first in {"where", "when", "why", "how", "what", "who", "which"}:
        notes.append(f"This sentence begins with the WH-word '{words[0].text}'.")

    if first == "where":
        notes.append("The speaker is asking about location.")

    if main_verb is not None and (main_verb.lemma or "").lower() == "be":
        notes.append(f"'{main_verb.text}' functions as the main linking verb or auxiliary here.")

    if not notes:
        notes.append("The sentence uses a simple standard structure.")

    return notes[:3]


# =========================
# Description helpers (AI-first)
# =========================
@lru_cache(maxsize=2048)
def _generate_explanation_english_cached(english_text):
    prompt = (
        "Explain the meaning of this sentence in one short declarative sentence.\n"
        "It must begin with 'The speaker'.\n"
        "Do not rewrite it as another question.\n"
        "Do not repeat the sentence exactly.\n"
        "Do not invent new objects or details.\n"
        "Keep the meaning faithful to the sentence.\n\n"
        f"Sentence: {english_text}\n\n"
        "Meaning:"
    )

    explanation = _generate_with_explainer(prompt, max_new_tokens=64, keep_newlines=False)

    src_norm = re.sub(r"\s+", " ", english_text.lower()).strip(" .!?")
    out_norm = re.sub(r"\s+", " ", explanation.lower()).strip(" .!?")

    too_close = (
        out_norm == src_norm
        or out_norm in src_norm
        or src_norm in out_norm
    )

    if _looks_garbled(explanation) or too_close or explanation.strip().endswith("?"):
        retry_prompt = (
            "Write one accurate declarative sentence explaining the meaning.\n"
            "It must begin with 'The speaker'.\n"
            "Do not write a question.\n"
            "Do not copy the sentence.\n"
            "Do not invent details.\n\n"
            f"Sentence: {english_text}\n\n"
            "Meaning:"
        )
        explanation = _generate_with_explainer(retry_prompt, max_new_tokens=72, keep_newlines=False)

    if _looks_garbled(explanation) or explanation.strip().endswith("?"):
        explanation = _fallback_description_from_parse(english_text)

    return explanation


def _generate_explanation_english(english_text):
    return _generate_explanation_english_cached(str(english_text))


def sentence_descriptions(text, source_lang, target_lang, translated_text=None):
    source_tag = resolve_lang_code(source_lang, text=text)
    target_tag = resolve_lang_code(target_lang)

    if translated_text is None:
        translated_text = None if source_tag == target_tag == "eng_Latn" else translated_text

    if source_tag == "eng_Latn":
        english_input = text
    elif target_tag == "eng_Latn" and translated_text:
        english_input = translated_text
    else:
        english_input = _generate_translation(
            text, source_tag, "eng_Latn", max_new_tokens=120
        )

    english_explanation = _generate_explanation_english(english_input)

    if source_tag == "eng_Latn":
        source_description = english_explanation
    else:
        source_description = _generate_translation(
            english_explanation, "eng_Latn", source_tag, max_new_tokens=120
        )

    if target_tag == "eng_Latn":
        target_description = english_explanation
    else:
        target_description = _generate_translation(
            english_explanation, "eng_Latn", target_tag, max_new_tokens=120
        )

    return {
        "english_explanation": english_explanation,
        "source_description": source_description,
        "target_description": target_description,
    }


def print_sentence_descriptions(text, source_lang, target_lang, translated_text=None):
    descriptions = sentence_descriptions(
        text, source_lang, target_lang, translated_text=translated_text
    )

    source_tag = resolve_lang_code(source_lang, text=text)
    target_tag = resolve_lang_code(target_lang)

    print("Sentence description:")
    print(f"- English explanation: {descriptions['english_explanation']}")
    print(f"- Source language ({source_tag}): {descriptions['source_description']}")
    print(f"- Target language ({target_tag}): {descriptions['target_description']}")
    print()

    return descriptions


# =========================
# Grammar analysis (grounded facts + AI notes)
# =========================
@lru_cache(maxsize=2048)
def _generate_ai_grammar_notes_cached(english_text, tense, structure, subject, verb, obj):
    prompt = (
        "Given these grammar facts, write 2 or 3 short grammar notes.\n"
        "Be accurate, helpful, and specific.\n"
        "Do not repeat the full sentence.\n"
        "Return only bullet points, each starting with '- '.\n\n"
        f"Sentence: {english_text}\n"
        f"Tense: {tense}\n"
        f"Structure: {structure}\n"
        f"Subject: {subject}\n"
        f"Verb: {verb}\n"
        f"Object: {obj}\n\n"
        "Grammar Notes:"
    )

    notes_text = _generate_with_explainer(prompt, max_new_tokens=80, keep_newlines=True)

    if _looks_garbled(notes_text):
        return tuple()

    lines = [line.strip() for line in notes_text.splitlines() if line.strip()]
    lines = [line if line.startswith("- ") else f"- {line.lstrip('- ').strip()}" for line in lines]
    lines = [line for line in lines if len(line) > 2]

    return tuple(lines[:3])


def _generate_ai_grammar_notes(english_text, tense, structure, subject, verb, obj):
    return list(
        _generate_ai_grammar_notes_cached(
            str(english_text),
            str(tense),
            str(structure),
            str(subject),
            str(verb),
            str(obj),
        )
    )


@lru_cache(maxsize=1024)
def _generate_grammar_analysis_english_cached(english_text):
    words = _get_en_words(english_text)

    if not words:
        return (
            "Grammar\n"
            "Tense: Unknown\n"
            "Structure: Unknown\n"
            "Subject: Unknown\n"
            "Verb: Unknown\n"
            "Object: Unknown\n"
            "Grammar Notes:\n"
            "- The sentence could not be parsed."
        )

    children = _build_children_map(words)
    root = _find_root(words)
    main_verb = _find_main_verb(words, root, children)

    tense = _infer_tense(words, root, children)
    structure = _infer_structure(words)
    subject = _find_subject_phrase(words, children)
    verb = main_verb.text if main_verb is not None else "Unknown"
    obj = _find_object_phrase(words, children)

    ai_notes = _generate_ai_grammar_notes(
        english_text=english_text,
        tense=tense,
        structure=structure,
        subject=subject,
        verb=verb,
        obj=obj,
    )

    if not ai_notes:
        ai_notes = [f"- {note}" for note in _default_grammar_notes(words, root, children)]

    lines = [
        "Grammar",
        f"Tense: {tense}",
        f"Structure: {structure}",
        f"Subject: {subject}",
        f"Verb: {verb}",
        f"Object: {obj}",
        "Grammar Notes:",
    ]
    lines.extend(ai_notes)

    return "\n".join(lines)


def _generate_grammar_analysis_english(english_text):
    return _generate_grammar_analysis_english_cached(str(english_text))


def sentence_grammar_analysis(text, source_lang, target_lang, translated_text=None):
    source_tag = resolve_lang_code(source_lang, text=text)
    target_tag = resolve_lang_code(target_lang)

    if source_tag == "eng_Latn":
        english_input = text
    elif target_tag == "eng_Latn" and translated_text:
        english_input = translated_text
    else:
        english_input = _generate_translation(
            text, source_tag, "eng_Latn", max_new_tokens=120
        )

    english_grammar = _generate_grammar_analysis_english(english_input)

    if source_tag == "eng_Latn":
        source_grammar = english_grammar
    else:
        source_grammar = _generate_translation(
            english_grammar, "eng_Latn", source_tag, max_new_tokens=260
        )

    if target_tag == "eng_Latn":
        target_grammar = english_grammar
    else:
        target_grammar = _generate_translation(
            english_grammar, "eng_Latn", target_tag, max_new_tokens=260
        )

    return {
        "english_grammar": english_grammar,
        "source_grammar": source_grammar,
        "target_grammar": target_grammar,
    }


def print_sentence_grammar_analysis(text, source_lang, target_lang, translated_text=None):
    grammar = sentence_grammar_analysis(
        text, source_lang, target_lang, translated_text=translated_text
    )

    source_tag = resolve_lang_code(source_lang, text=text)
    target_tag = resolve_lang_code(target_lang)

    print("Grammar analysis:")
    print(f"- English:\n{grammar['english_grammar']}\n")
    print(f"- Source language ({source_tag}):\n{grammar['source_grammar']}\n")
    print(f"- Target language ({target_tag}):\n{grammar['target_grammar']}\n")

    return grammar


# =========================
# Main translation APIs
# =========================
def translate(text, source_lang, target_lang, max_new_tokens=200):
    source_tag = resolve_lang_code(source_lang, text=text)
    target_tag = resolve_lang_code(target_lang)

    start = time.time()
    result = _generate_translation(
        text, source_tag, target_tag, max_new_tokens=max_new_tokens
    )
    end = time.time()

    print(f"\nInput ({source_tag}): {text}")
    print(f"Translated ({target_tag}): {result}")
    print(f"Time taken: {end - start:.2f} seconds\n")

    print_word_meanings(text, source_tag, target_tag, translated_text=result)
    print_sentence_descriptions(text, source_tag, target_tag, translated_text=result)
    print_sentence_grammar_analysis(text, source_tag, target_tag, translated_text=result)

    return result


def auto_translate(text, target_lang, max_new_tokens=200, min_confidence=0.30):
    detected_lang, confidence = detect_language(text)

    if confidence < min_confidence:
        print("Detection confidence is low. Consider using explicit source_lang.")
        return None

    try:
        source_tag = resolve_lang_code(detected_lang, text=text)
    except ValueError:
        print(f"Auto-detect language not mapped: {detected_lang}")
        print("Use explicit source_lang, e.g. translate(text, 'vie_Latn', 'urd_Arab')")
        return None

    return translate(text, source_tag, target_lang, max_new_tokens=max_new_tokens)


# =========================
# Optional helpers
# =========================
def list_supported_languages():
    for lang in ALL_NLLB_LANGS:
        print(lang)
    return ALL_NLLB_LANGS


def list_aliases():
    for k in sorted(LANGUAGE_ALIASES):
        print(f"{k} -> {LANGUAGE_ALIASES[k]}")
    return LANGUAGE_ALIASES


# =========================
# Example
# =========================
if __name__ == "__main__":
    auto_translate("Wo ist deine Tasche?", "english")