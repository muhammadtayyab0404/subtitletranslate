from transformers import AutoModelForSeq2SeqLM, AutoTokenizer
import torch
import fasttext
import time

# =========================
# Load Translation Model
# =========================

model_name = "facebook/nllb-200-distilled-600M"

print("Loading translation model...")
tokenizer = AutoTokenizer.from_pretrained(model_name)
model = AutoModelForSeq2SeqLM.from_pretrained(model_name)

model.config.tie_word_embeddings = False

device = "cpu"
model = model.to(device)   # removed .half() (better for CPU)
model.eval()

# =========================
# Load Language Detector
# =========================

print("Loading language detector...")
detector = fasttext.load_model("lid.176.bin")

# =========================
# ISO → NLLB Mapping
# =========================

language_map = {
    "en": "eng_Latn",
    "ur": "urd_Arab",
    "de": "deu_Latn",
    "fr": "fra_Latn",
    "es": "spa_Latn",
    "hi": "hin_Deva",
    "ar": "arb_Arab",
    "ru": "rus_Cyrl",
    "ja": "jpn_Jpan",
    "ko": "kor_Hang",
    "tr": "tur_Latn",
    "zh": "zho_Hans",  # default simplified
}

# =========================
# Detect Language
# =========================

def detect_language(text):
    prediction = detector.predict(text)
    lang = prediction[0][0].replace("__label__", "")
    confidence = prediction[1][0]

    print(f"Detected language: {lang} (confidence: {round(confidence, 2)})")
    return lang, confidence

# =========================
# Translation Function
# =========================

def translate(text, source_lang, target_lang):
    tokenizer.src_lang = source_lang

    inputs = tokenizer(text, return_tensors="pt").to(device)

    forced_bos_token_id = tokenizer.convert_tokens_to_ids(target_lang)

    with torch.no_grad():
        start = time.time()
        generated_tokens = model.generate(
            **inputs,
            forced_bos_token_id=forced_bos_token_id,
            max_length=200,
            num_beams=1,
            do_sample=False
        )
        result = tokenizer.batch_decode(generated_tokens, skip_special_tokens=True)[0]
        end = time.time()

    print(f"\nInput ({source_lang}): {text}")
    print(f"Translated ({target_lang}): {result}")
    print("Time taken:", round(end - start, 2), "seconds\n")

    return result

# =========================
# Auto Translate Function
# =========================

def auto_translate(text, target_lang):
    detected_lang, confidence = detect_language(text)

    if detected_lang not in language_map:
        print("Language not supported:", detected_lang)
        return None

    source_nllb = language_map[detected_lang]

    return translate(text, source_nllb, target_lang)


# =========================
# TEST
# =========================

auto_translate("私の人生にはやるべきことがたくさんあります。", "eng_Latn")




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
