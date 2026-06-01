import time
import torch
import fasttext

from transformers import AutoConfig, AutoModelForSeq2SeqLM, AutoTokenizer
from transformers.models.nllb.tokenization_nllb import FAIRSEQ_LANGUAGE_CODES

# =========================
# Config
# =========================
MODEL_NAME = "facebook/nllb-200-distilled-600M"

# =========================
# Load Translation Model
# =========================
print("Loading translation model...")

config = AutoConfig.from_pretrained(MODEL_NAME)
config.tie_word_embeddings = False  # set before model load

tokenizer = AutoTokenizer.from_pretrained(MODEL_NAME)
model = AutoModelForSeq2SeqLM.from_pretrained(MODEL_NAME, config=config)

device = "cuda" if torch.cuda.is_available() else "cpu"
model = model.to(device)

if device == "cuda":
    model = model.half()

model.eval()

# =========================
# Load Language Detector
# =========================
print("Loading language detector...")
detector = fasttext.load_model("lid.176.bin")

# =========================
# Full NLLB language list
# =========================
ALL_NLLB_LANGS = sorted(FAIRSEQ_LANGUAGE_CODES)
ALL_NLLB_LANGS_SET = set(ALL_NLLB_LANGS)

print(f"Loaded {len(ALL_NLLB_LANGS)} NLLB language tags.")

# canonical lower-case tag lookup
CANONICAL_TAG_LOOKUP = {tag.lower(): tag for tag in ALL_NLLB_LANGS}

# Build base -> variants
# Example: "zho" -> ["zho_Hans", "zho_Hant"]
BASE_TO_VARIANTS = {}
for tag in ALL_NLLB_LANGS:
    base = tag.split("_")[0].lower()
    BASE_TO_VARIANTS.setdefault(base, []).append(tag)

# Defaults for ambiguous bases
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
# Separate alias layer
# =========================
LANGUAGE_ALIASES = {
    # -------- 2-letter / fastText-style aliases --------
    "af": "afr_Latn",
    "am": "amh_Ethi",
    "ar": "arb_Arab",
    "az": "azj_Latn",
    "be": "bel_Cyrl",
    "bg": "bul_Cyrl",
    "bn": "ben_Beng",
    "bs": "bos_Latn",
    "ca": "cat_Latn",
    "cs": "ces_Latn",
    "cy": "cym_Latn",
    "da": "dan_Latn",
    "de": "deu_Latn",
    "el": "ell_Grek",
    "en": "eng_Latn",
    "eo": "epo_Latn",
    "es": "spa_Latn",
    "et": "est_Latn",
    "eu": "eus_Latn",
    "fa": "pes_Arab",
    "fi": "fin_Latn",
    "fr": "fra_Latn",
    "ga": "gle_Latn",
    "gl": "glg_Latn",
    "gu": "guj_Gujr",
    "he": "heb_Hebr",
    "hi": "hin_Deva",
    "hr": "hrv_Latn",
    "hu": "hun_Latn",
    "hy": "hye_Armn",
    "id": "ind_Latn",
    "is": "isl_Latn",
    "it": "ita_Latn",
    "ja": "jpn_Jpan",
    "jv": "jav_Latn",
    "ka": "kat_Geor",
    "kk": "kaz_Cyrl",
    "km": "khm_Khmr",
    "ko": "kor_Hang",
    "ky": "kir_Cyrl",
    "lo": "lao_Laoo",
    "lt": "lit_Latn",
    "lv": "lvs_Latn",
    "ml": "mal_Mlym",
    "mr": "mar_Deva",
    "ms": "zsm_Latn",
    "mt": "mlt_Latn",
    "my": "mya_Mymr",
    "ne": "npi_Deva",
    "nl": "nld_Latn",
    "nn": "nno_Latn",
    "no": "nob_Latn",
    "or": "ory_Orya",
    "pa": "pan_Guru",
    "pl": "pol_Latn",
    "ps": "pbt_Arab",
    "pt": "por_Latn",
    "ro": "ron_Latn",
    "ru": "rus_Cyrl",
    "sd": "snd_Arab",
    "si": "sin_Sinh",
    "sk": "slk_Latn",
    "sl": "slv_Latn",
    "so": "som_Latn",
    "sq": "als_Latn",
    "sr": "srp_Cyrl",
    "sv": "swe_Latn",
    "sw": "swh_Latn",
    "ta": "tam_Taml",
    "te": "tel_Telu",
    "tg": "tgk_Cyrl",
    "th": "tha_Thai",
    "tk": "tuk_Latn",
    "tl": "tgl_Latn",
    "tr": "tur_Latn",
    "ug": "uig_Arab",
    "uk": "ukr_Cyrl",
    "ur": "urd_Arab",
    "uz": "uzn_Latn",
    "vi": "vie_Latn",
    "xh": "xho_Latn",
    "yo": "yor_Latn",
    "zh": "zho_Hans",
    "zu": "zul_Latn",

    # -------- common English names --------
    "afrikaans": "afr_Latn",
    "amharic": "amh_Ethi",
    "arabic": "arb_Arab",
    "armenian": "hye_Armn",
    "azerbaijani": "azj_Latn",
    "bengali": "ben_Beng",
    "bulgarian": "bul_Cyrl",
    "catalan": "cat_Latn",
    "chinese": "zho_Hans",
    "simplified_chinese": "zho_Hans",
    "traditional_chinese": "zho_Hant",
    "croatian": "hrv_Latn",
    "czech": "ces_Latn",
    "danish": "dan_Latn",
    "dutch": "nld_Latn",
    "english": "eng_Latn",
    "estonian": "est_Latn",
    "farsi": "pes_Arab",
    "persian": "pes_Arab",
    "finnish": "fin_Latn",
    "french": "fra_Latn",
    "georgian": "kat_Geor",
    "german": "deu_Latn",
    "greek": "ell_Grek",
    "gujarati": "guj_Gujr",
    "hebrew": "heb_Hebr",
    "hindi": "hin_Deva",
    "hungarian": "hun_Latn",
    "icelandic": "isl_Latn",
    "indonesian": "ind_Latn",
    "irish": "gle_Latn",
    "italian": "ita_Latn",
    "japanese": "jpn_Jpan",
    "javanese": "jav_Latn",
    "kazakh": "kaz_Cyrl",
    "khmer": "khm_Khmr",
    "korean": "kor_Hang",
    "kyrgyz": "kir_Cyrl",
    "lao": "lao_Laoo",
    "latvian": "lvs_Latn",
    "lithuanian": "lit_Latn",
    "macedonian": "mkd_Cyrl",
    "malay": "zsm_Latn",
    "malayalam": "mal_Mlym",
    "marathi": "mar_Deva",
    "mongolian": "khk_Cyrl",
    "nepali": "npi_Deva",
    "norwegian": "nob_Latn",
    "nynorsk": "nno_Latn",
    "bokmal": "nob_Latn",
    "odia": "ory_Orya",
    "oriya": "ory_Orya",
    "pashto": "pbt_Arab",
    "polish": "pol_Latn",
    "portuguese": "por_Latn",
    "punjabi": "pan_Guru",
    "romanian": "ron_Latn",
    "russian": "rus_Cyrl",
    "serbian": "srp_Cyrl",
    "sindhi": "snd_Arab",
    "sinhala": "sin_Sinh",
    "sinhalese": "sin_Sinh",
    "slovak": "slk_Latn",
    "slovenian": "slv_Latn",
    "somali": "som_Latn",
    "spanish": "spa_Latn",
    "swahili": "swh_Latn",
    "swedish": "swe_Latn",
    "tajik": "tgk_Cyrl",
    "tamil": "tam_Taml",
    "telugu": "tel_Telu",
    "thai": "tha_Thai",
    "turkish": "tur_Latn",
    "ukrainian": "ukr_Cyrl",
    "urdu": "urd_Arab",
    "uyghur": "uig_Arab",
    "uzbek": "uzn_Latn",
    "vietnamese": "vie_Latn",
    "welsh": "cym_Latn",
    "xhosa": "xho_Latn",
    "yoruba": "yor_Latn",
    "zulu": "zul_Latn",

    # -------- helpful special cases --------
    "mandarin": "zho_Hans",
    "cantonese": "yue_Hant",
    "quechua": "quy_Latn",
    "albanian": "als_Latn",
    "kurdish": "kmr_Latn",
    "kurmanji": "kmr_Latn",
    "sorani": "ckb_Arab",
}

# also allow canonical tags in lowercase, e.g. "eng_latn"
for tag in ALL_NLLB_LANGS:
    LANGUAGE_ALIASES.setdefault(tag.lower(), tag)

# also allow 3-letter NLLB bases, e.g. "vie" -> "vie_Latn"
for base, default_tag in BASE_TO_DEFAULT.items():
    if default_tag:
        LANGUAGE_ALIASES.setdefault(base, default_tag)

# =========================
# Script helpers for ambiguous bases
# =========================
def _has_char_in_ranges(text, ranges):
    for ch in text:
        cp = ord(ch)
        for start, end in ranges:
            if start <= cp <= end:
                return True
    return False

ARABIC_RANGES = [
    (0x0600, 0x06FF),
    (0x0750, 0x077F),
    (0x08A0, 0x08FF),
    (0xFB50, 0xFDFF),
    (0xFE70, 0xFEFF),
]

DEVANAGARI_RANGES = [
    (0x0900, 0x097F),
]

TIFINAGH_RANGES = [
    (0x2D30, 0x2D7F),
]

def choose_ambiguous_variant(base, text):
    if base == "kas":
        if _has_char_in_ranges(text, DEVANAGARI_RANGES):
            return "kas_Deva"
        return "kas_Arab"

    if base in {"ace", "bjn", "knc"}:
        if _has_char_in_ranges(text, ARABIC_RANGES):
            return f"{base}_Arab"
        return f"{base}_Latn"

    if base == "taq":
        if _has_char_in_ranges(text, TIFINAGH_RANGES):
            return "taq_Tfng"
        return "taq_Latn"

    if base == "zho":
        return "zho_Hans"

    return BASE_TO_DEFAULT.get(base)

# =========================
# Resolution helpers
# =========================
def normalize_lang_key(code: str) -> str:
    return code.strip().lower().replace("-", "_").replace(" ", "_")

def resolve_lang_code(code, text=""):
    """
    Accepts:
      - full NLLB tag:  'vie_Latn'
      - lowercase tag:  'vie_latn'
      - base code:      'vie'
      - 2-letter code:  'vi'
      - language name:  'vietnamese'
    Returns:
      - full NLLB tag
    """
    if not isinstance(code, str) or not code.strip():
        raise ValueError("Language code must be a non-empty string.")

    # exact canonical tag
    if code in ALL_NLLB_LANGS_SET:
        return code

    key = normalize_lang_key(code)

    # lowercase canonical tag, e.g. eng_latn
    if key in CANONICAL_TAG_LOOKUP:
        return CANONICAL_TAG_LOOKUP[key]

    # bare 3-letter NLLB base, e.g. "kas", "zho"
    if key in BASE_TO_VARIANTS:
        variants = BASE_TO_VARIANTS[key]
        if len(variants) == 1:
            return variants[0]
        chosen = choose_ambiguous_variant(key, text)
        if chosen:
            return chosen

    # aliases / names / short codes
    if key in LANGUAGE_ALIASES:
        alias_value = LANGUAGE_ALIASES[key]

        # allow alias to point to a base code if you ever add that later
        if alias_value in BASE_TO_VARIANTS:
            variants = BASE_TO_VARIANTS[alias_value]
            if len(variants) == 1:
                return variants[0]
            chosen = choose_ambiguous_variant(alias_value, text)
            if chosen:
                return chosen

        return alias_value

    raise ValueError(
        f"Unsupported or unmapped language code: {code}\n"
        f"Use a full NLLB tag like 'vie_Latn'."
    )

def list_supported_languages():
    for lang in ALL_NLLB_LANGS:
        print(lang)
    return ALL_NLLB_LANGS

def list_aliases():
    for k in sorted(LANGUAGE_ALIASES):
        print(f"{k} -> {LANGUAGE_ALIASES[k]}")
    return LANGUAGE_ALIASES

# =========================
# Detection
# =========================
def detect_language(text):
    clean_text = " ".join(text.split())
    labels, scores = detector.predict(clean_text)
    lang = labels[0].replace("__label__", "")
    confidence = float(scores[0])

    print(f"Detected language: {lang} (confidence: {confidence:.2f})")
    return lang, confidence

# =========================
# Translation
# =========================
def translate(text, source_lang, target_lang, max_new_tokens=200):
    """
    Full-coverage path:
    You can pass full tags, short codes, 3-letter bases, or names.
    """
    source_tag = resolve_lang_code(source_lang, text=text)
    target_tag = resolve_lang_code(target_lang)

    tokenizer.src_lang = source_tag
    inputs = tokenizer(text, return_tensors="pt").to(device)

    forced_bos_token_id = tokenizer.convert_tokens_to_ids(target_tag)

    with torch.inference_mode():
        start = time.time()
        generated_tokens = model.generate(
            **inputs,
            forced_bos_token_id=forced_bos_token_id,
            max_new_tokens=max_new_tokens,
            num_beams=1,
            do_sample=False
        )
        result = tokenizer.batch_decode(generated_tokens, skip_special_tokens=True)[0]
        end = time.time()

    print(f"\nInput ({source_tag}): {text}")
    print(f"Translated ({target_tag}): {result}")
    print(f"Time taken: {end - start:.2f} seconds\n")
    return result

def auto_translate(text, target_lang, max_new_tokens=200, min_confidence=0.30):
    """
    Best-effort auto-detect path.
    If detection is weak or alias is missing, use explicit translate(...).
    """
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
# Examples
# =========================
if __name__ == "__main__":
    # explicit full-tag mode
    translate("Chào, tôi phải đi ngay bây giờ", "vie_Latn", "urd_Arab")

    # short-code mode
    translate("Ich baue mein eigenes System.", "de", "ur")

    # name-alias mode
    translate("Sometimes life says so. now it's okay.", "english", "japanese")

    # auto-detect mode
    auto_translate("Chào, tôi phải đi ngay bây giờ", "urdu")

    # debugging helpers
    # list_supported_languages()
    # list_aliases()
    # Print every supported NLLB tag
    # list_supported_languages()

    # list_supported_languages()
    # Full explicit mode supports all model language tags
    # translate("私の人生にはやるべきことがたくさんあります。", "jpn_Jpan", "eng_Latn")
    # print(list_supported_languages())
    # Full explicit mode = supports all NLLB tags
    # translate("私の人生にはやるべきことがたくさんあります。", "jpn_Jpan", "eng_Latn")
    # translate("دل کہتا ہے کوئی میرا بھی دیوانہ بنے", "urd_Arab", "eng_Latn")
    # translate("Ich baue mein eigenes System.", "deu_Latn", "urd_Arab")

    # Auto-detect mode = best effort
    # auto_translate("Bonjour tout le monde", "eng_Latn")



# List all supported languages


# Test translations
# translate("Hello, how are you?", "eng_Latn", "urdu")
# translate("Artificial Intelligence is powerful.", "eng_Latn", "deu_Latn")
# translate("دل کہتا ہے کوئی میرا بھی دیوانہ بنے", "urd_Arab", "eng_Latn")

# translate("Sometimes life says so. now it's okay.", "eng_Latn", "urd_Arab")

##
# language_details: >-
#   ace_Arab, ace_Latn, acm_Arab, acq_Arab, aeb_Arab, afr_Latn, ajp_Arab,
#   aka_Latn, amh_Ethi, apc_Arab, arb_Arab, ars_Arab, ary_Arab, arz_Arab,
#   asm_Beng, ast_Latn, awa_Deva, ayr_Latn, azb_Arab, azj_Latn, bak_Cyrl,
#   bam_Latn, ban_Latn,bel_Cyrl, bem_Latn, ben_Beng, bho_Deva, bjn_Arab, bjn_Latn,
#   bod_Tibt, bos_Latn, bug_Latn, bul_Cyrl, cat_Latn, ceb_Latn, ces_Latn,
#   cjk_Latn, ckb_Arab, crh_Latn, cym_Latn, dan_Latn, deu_Latn, dik_Latn,
#   dyu_Latn, dzo_Tibt, ell_Grek, eng_Latn, epo_Latn, est_Latn, eus_Latn,
#   ewe_Latn, fao_Latn, pes_Arab, fij_Latn, fin_Latn, fon_Latn, fra_Latn,
#   fur_Latn, fuv_Latn, gla_Latn, gle_Latn, glg_Latn, grn_Latn, guj_Gujr,
#   hat_Latn, hau_Latn, heb_Hebr, hin_Deva, hne_Deva, hrv_Latn, hun_Latn,
#   hye_Armn, ibo_Latn, ilo_Latn, ind_Latn, isl_Latn, ita_Latn, jav_Latn,
#   jpn_Jpan, kab_Latn, kac_Latn, kam_Latn, kan_Knda, kas_Arab, kas_Deva,
#   kat_Geor, knc_Arab, knc_Latn, kaz_Cyrl, kbp_Latn, kea_Latn, khm_Khmr,
#   kik_Latn, kin_Latn, kir_Cyrl, kmb_Latn, kon_Latn, kor_Hang, kmr_Latn,
#   lao_Laoo, lvs_Latn, lij_Latn, lim_Latn, lin_Latn, lit_Latn, lmo_Latn,
#   ltg_Latn, ltz_Latn, lua_Latn, lug_Latn, luo_Latn, lus_Latn, mag_Deva,
#   mai_Deva, mal_Mlym, mar_Deva, min_Latn, mkd_Cyrl, plt_Latn, mlt_Latn,
#   mni_Beng, khk_Cyrl, mos_Latn, mri_Latn, zsm_Latn, mya_Mymr, nld_Latn,
#   nno_Latn, nob_Latn, npi_Deva, nso_Latn, nus_Latn, nya_Latn, oci_Latn,
#   gaz_Latn, ory_Orya, pag_Latn, pan_Guru, pap_Latn, pol_Latn, por_Latn,
#   prs_Arab, pbt_Arab, quy_Latn, ron_Latn, run_Latn, rus_Cyrl, sag_Latn,
#   san_Deva, sat_Beng, scn_Latn, shn_Mymr, sin_Sinh, slk_Latn, slv_Latn,
#   smo_Latn, sna_Latn, snd_Arab, som_Latn, sot_Latn, spa_Latn, als_Latn,
#   srd_Latn, srp_Cyrl, ssw_Latn, sun_Latn, swe_Latn, swh_Latn, szl_Latn,
#   tam_Taml, tat_Cyrl, tel_Telu, tgk_Cyrl, tgl_Latn, tha_Thai, tir_Ethi,
#   taq_Latn, taq_Tfng, tpi_Latn, tsn_Latn, tso_Latn, tuk_Latn, tum_Latn,
#   tur_Latn, twi_Latn, tzm_Tfng, uig_Arab, ukr_Cyrl, umb_Latn, urd_Arab,
#   uzn_Latn, vec_Latn, vie_Latn, war_Latn, wol_Latn, xho_Latn, ydd_Hebr,
#   yor_Latn, yue_Hant, zho_Hans, zho_Hant, zul_Latn
# pipeline_tag: translation
