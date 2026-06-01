import re
import time
import torch
import fasttext
from simalign import SentenceAligner

from transformers import AutoConfig, AutoModelForSeq2SeqLM, AutoTokenizer
from transformers.models.nllb.tokenization_nllb import FAIRSEQ_LANGUAGE_CODES

# =========================
# Config
# =========================
MODEL_NAME = "facebook/nllb-200-distilled-600M"
EXPLAINER_MODEL_NAME = "google/flan-t5-small"   # lightweight
# For better quality (heavier): "google/flan-t5-base"

# =========================
# Load Translation Model
# =========================
print("Loading translation model...")

translation_config = AutoConfig.from_pretrained(MODEL_NAME)
translation_config.tie_word_embeddings = False

tokenizer = AutoTokenizer.from_pretrained(MODEL_NAME)
model = AutoModelForSeq2SeqLM.from_pretrained(MODEL_NAME, config=translation_config)

device = "cuda" if torch.cuda.is_available() else "cpu"
model = model.to(device)

if device == "cuda":
    model = model.half()

model.eval()

# =========================
# Load Explanation Model
# =========================
print("Loading explanation model...")

explainer_config = AutoConfig.from_pretrained(EXPLAINER_MODEL_NAME)
explainer_config.tie_word_embeddings = False

explainer_tokenizer = AutoTokenizer.from_pretrained(EXPLAINER_MODEL_NAME)
explainer_model = AutoModelForSeq2SeqLM.from_pretrained(
    EXPLAINER_MODEL_NAME,
    config=explainer_config
)

explainer_model = explainer_model.to(device)

if device == "cuda":
    explainer_model = explainer_model.half()

explainer_model.eval()

# =========================
# Load Language Detector
# =========================
print("Loading language detector...")
detector = fasttext.load_model("lid.176.bin")

# =========================
# Load Contextual Word Aligner
# =========================
print("Loading contextual word aligner...")
aligner = SentenceAligner(model="bert", token_type="bpe", matching_methods="mai")

# =========================
# Full NLLB language list
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

# =========================
# Alias layer
# =========================
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
# Explainer cleanup / guardrails
# =========================
def _make_bad_words_ids(tok, chunks):
    bad_ids = []
    for chunk in chunks:
        ids = tok(chunk, add_special_tokens=False).input_ids
        if ids:
            bad_ids.append(ids)
    return bad_ids

EXPLAINER_BAD_WORDS = _make_bad_words_ids(explainer_tokenizer, [
    "http", "https", "www", ".com", ".org",
    "-LRB-", "-RRB-", "-LSB-", "-RSB-",
    "&quot;", "&amp;"
])

def _cleanup_generated_text(text):
    text = re.sub(r"https?://\S+|www\.\S+", "", text)

    replacements = {
        "-LRB-": "(",
        "-RRB-": ")",
        "-LSB-": "[",
        "-RSB-": "]",
        "&quot;": '"',
        "&amp;": "&",
    }
    for old, new in replacements.items():
        text = text.replace(old, new)

    text = re.sub(r"\s+", " ", text).strip()
    return text.strip(" -")

def _generate_with_explainer(prompt, max_new_tokens=120, min_new_tokens=20):
    inputs = explainer_tokenizer(prompt, return_tensors="pt").to(device)

    with torch.inference_mode():
        outputs = explainer_model.generate(
            **inputs,
            max_new_tokens=max_new_tokens,
            min_new_tokens=min_new_tokens,
            num_beams=5,
            do_sample=False,
            no_repeat_ngram_size=3,
            repetition_penalty=1.2,
            bad_words_ids=EXPLAINER_BAD_WORDS,
            early_stopping=True
        )

    text = explainer_tokenizer.batch_decode(outputs, skip_special_tokens=True)[0]
    return _cleanup_generated_text(text)

# =========================
# Detection
# =========================
def detect_language(text):
    clean_text = " ".join(text.split())
    labels, scores = detector.predict(clean_text)

    first_label = labels[0]
    if not isinstance(first_label, str):
        first_label = first_label[0]

    try:
        first_score = float(scores[0])
    except Exception:
        first_score = float(scores)

    lang = first_label.replace("__label__", "")
    confidence = first_score

    print(f"Detected language: {lang} (confidence: {confidence:.2f})")
    return lang, confidence
    clean_text = " ".join(text.split())
    labels, scores = detector.predict(clean_text)

    lang = labels[0].replace("__label__", "")
    confidence = float(scores[0])

    print(f"Detected language: {lang} (confidence: {confidence:.2f})")
    return lang, confidence
# =========================
# Internal translation core
# =========================
def _generate_translation(text, source_tag, target_tag, max_new_tokens=200):
    tokenizer.src_lang = source_tag
    inputs = tokenizer(text, return_tensors="pt").to(device)
    forced_bos_token_id = tokenizer.convert_tokens_to_ids(target_tag)

    with torch.inference_mode():
        generated_tokens = model.generate(
            **inputs,
            forced_bos_token_id=forced_bos_token_id,
            max_new_tokens=max_new_tokens,
            num_beams=1,
            do_sample=False
        )
        result = tokenizer.batch_decode(generated_tokens, skip_special_tokens=True)[0]

    return result

# =========================
# Tokenization helpers
# =========================
WORD_RE = re.compile(r"\w+|[^\w\s]", re.UNICODE)

def split_text_for_meanings(text):
    parts = WORD_RE.findall(text)
    return [p for p in parts if p.strip()]

def _is_punctuation_only(token):
    return bool(re.fullmatch(r"[^\w\s]+", token, re.UNICODE))

# =========================
# Context-aware word meanings
# =========================
def word_meanings(text, source_lang, target_lang, translated_text=None):
    source_tag = resolve_lang_code(source_lang, text=text)
    target_tag = resolve_lang_code(target_lang)

    if translated_text is None:
        translated_text = _generate_translation(text, source_tag, target_tag)

    src_tokens = split_text_for_meanings(text)
    tgt_tokens = split_text_for_meanings(translated_text)

    if not src_tokens or not tgt_tokens:
        return []

    try:
        alignments = aligner.get_word_aligns(src_tokens, tgt_tokens)
    except Exception:
        return []

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

    return results

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
# Improved AI sentence description
# =========================
def _generate_explanation_english(english_text):
    prompt = (
        "Explain the meaning of this sentence in simple English.\n"
        "Do not repeat the sentence.\n"
        "Do not include links, symbols, website text, or random text.\n"
        "Describe what the speaker means in exactly 2 short sentences.\n\n"
        f"Sentence: {english_text}\n\n"
        "Explanation:"
    )

    explanation = _generate_with_explainer(
        prompt,
        max_new_tokens=120,
        min_new_tokens=22
    )

    src_norm = re.sub(r"\s+", " ", english_text.lower()).strip(" .!?")
    out_norm = re.sub(r"\s+", " ", explanation.lower()).strip(" .!?")

    bad_pattern = (
        not explanation
        or "http" in out_norm
        or "www" in out_norm
        or "-lrb-" in out_norm
        or "-rrb-" in out_norm
        or out_norm == src_norm
        or out_norm in src_norm
        or src_norm in out_norm
    )

    if bad_pattern:
        retry_prompt = (
            "Read the sentence and explain its meaning clearly.\n"
            "Do not rewrite the sentence.\n"
            "Do not add links, symbols, code, or extra random text.\n"
            "Say what the speaker is asking, saying, or intending.\n"
            "Write exactly 2 short explanatory sentences.\n\n"
            f"Sentence: {english_text}\n\n"
            "Clear explanation:"
        )

        explanation = _generate_with_explainer(
            retry_prompt,
            max_new_tokens=140,
            min_new_tokens=24
        )

    return explanation

def sentence_descriptions(text, source_lang, target_lang, translated_text=None):
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

    english_explanation = _generate_explanation_english(english_input)

    if source_tag == "eng_Latn":
        source_description = english_explanation
    else:
        source_description = _generate_translation(
            english_explanation, "eng_Latn", source_tag, max_new_tokens=180
        )

    if target_tag == "eng_Latn":
        target_description = english_explanation
    else:
        target_description = _generate_translation(
            english_explanation, "eng_Latn", target_tag, max_new_tokens=180
        )

    return {
        "english_explanation": english_explanation,
        "source_description": source_description,
        "target_description": target_description,
    }

def print_sentence_descriptions(text, source_lang, target_lang, translated_text=None):
    descriptions = sentence_descriptions(
        text,
        source_lang,
        target_lang,
        translated_text=translated_text
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

    return translate(
        text,
        source_tag,
        target_lang,
        max_new_tokens=max_new_tokens
    )

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