/* Subtitle Genie — Vanilla (Tailwind + JS)
   This project is a plain HTML/CSS/JS rewrite of the original React app.
   Backend calls are mocked; wire these functions to your Laravel endpoints later.
*/

(function () {
  "use strict";

  // ---------- helpers ----------
  function $(sel, root) { return (root || document).querySelector(sel); }
  function $all(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }

  function getFileName() {
    const path = window.location.pathname || "";
    const last = path.split("/").filter(Boolean).pop() || "";
    if (last.endsWith(".html")) return last;
    // If served from a router, map / -> index.html, /working -> working.html, etc.
    if (!last) return "index.html";
    return last + ".html";
  }

  function bytesToSize(bytes) {
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
    return (bytes / (1024 * 1024)).toFixed(1) + " MB";
  }

  function createDownload(url, filename) {
    const link = document.createElement("a");
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  function setText(el, text) {
    if (!el) return;
    el.textContent = text;
  }

  function setHTML(el, html) {
    if (!el) return;
    el.innerHTML = html;
  }

  function setHidden(el, hidden) {
    if (!el) return;
    el.classList.toggle("hidden", !!hidden);
  }

  function setDisabled(el, disabled) {
    if (!el) return;
    el.toggleAttribute("disabled", !!disabled);
    el.classList.toggle("opacity-50", !!disabled);
    el.classList.toggle("cursor-not-allowed", !!disabled);
  }

  function reIconize() {
    // lucide (UMD) exposes `lucide` global.
    if (window.lucide && typeof window.lucide.createIcons === "function") {
      window.lucide.createIcons();
    }
  }

  // ---------- global UI ----------
  function setupNavbar() {
    const current = getFileName();
    $all("[data-nav-link]").forEach((a) => {
      const href = (a.getAttribute("href") || "").split("#")[0];
      const isActive = href === current || (href === "index.html" && current === "");
      a.classList.toggle("bg-primary/10", isActive);
      a.classList.toggle("text-primary", isActive);
      a.classList.toggle("font-semibold", isActive);
      a.classList.toggle("text-muted-foreground", !isActive);
    });

    const btn = $("#mobileMenuBtn");
    const panel = $("#mobileMenuPanel");
    if (!btn || !panel) return;

    function close() {
      panel.classList.add("hidden");
      btn.setAttribute("aria-expanded", "false");
      btn.dataset.open = "0";
      $("#iconMenu")?.classList.remove("hidden");
      $("#iconClose")?.classList.add("hidden");
    }

    function open() {
      panel.classList.remove("hidden");
      btn.setAttribute("aria-expanded", "true");
      btn.dataset.open = "1";
      $("#iconMenu")?.classList.add("hidden");
      $("#iconClose")?.classList.remove("hidden");
    }

    btn.addEventListener("click", () => {
      const isOpen = btn.dataset.open === "1";
      isOpen ? close() : open();
    });

    // close on link click
    $all("#mobileMenuPanel a").forEach((a) => a.addEventListener("click", close));
  }

  function setupAnimations() {
    const els = $all("[data-animate]");
    if (!els.length) return;

    const io = new IntersectionObserver(
      (entries) => {
        for (const e of entries) {
          if (e.isIntersecting) {
            e.target.classList.add("in");
            io.unobserve(e.target);
          }
        }
      },
      { threshold: 0.12 }
    );

    els.forEach((el) => io.observe(el));
  }

  // ---------- mock API (ported from src/lib/api.ts) ----------
  const jobs = new Map();

  function uploadFile(file) {
    return new Promise((resolve) => {
      setTimeout(() => {
        const jobId = "job_" + Date.now() + "_" + Math.random().toString(36).slice(2, 11);
        jobs.set(jobId, { startTime: Date.now() });
        resolve({ jobId });
      }, 500);
    });
  }

  function getJobStatus(jobId) {
    const job = jobs.get(jobId);
    if (!job) return Promise.resolve({ status: "error", progress: 0, error: "Job not found" });

    const elapsed = Date.now() - job.startTime;
    if (elapsed < 2000) {
      const progress = Math.min(100, Math.floor((elapsed / 2000) * 100));
      return Promise.resolve({ status: "uploading", progress });
    }
    if (elapsed < 8000) {
      const progress = Math.min(100, Math.floor(((elapsed - 2000) / 6000) * 100));
      return Promise.resolve({ status: "processing", progress });
    }
    return Promise.resolve({ status: "completed", progress: 100 });
  }

  function getJobResult(jobId) {
    // Small delay like the React mock.
    return new Promise((resolve) => {
      setTimeout(() => {
        const mockSrtContent = `1
00:00:00,000 --> 00:00:03,500
Welcome to our video tutorial.

2
00:00:03,500 --> 00:00:07,000
Today we'll be learning about AI subtitles.

3
00:00:07,000 --> 00:00:11,500
This technology uses advanced machine learning.`;

        const mockVttContent = `WEBVTT

00:00:00.000 --> 00:00:03.500
Welcome to our video tutorial.

00:00:03.500 --> 00:00:07.000
Today we'll be learning about AI subtitles.

00:00:07.000 --> 00:00:11.500
This technology uses advanced machine learning.`;

        resolve({
          srtUrl: `data:text/plain;charset=utf-8,${encodeURIComponent(mockSrtContent)}`,
          vttUrl: `data:text/plain;charset=utf-8,${encodeURIComponent(mockVttContent)}`,
          transcriptText:
            "Welcome to our video tutorial. Today we'll be learning about AI subtitles. This technology uses advanced machine learning to convert speech to text with high accuracy. Let's get started with the demonstration.",
          preview: {
            startText: "Welcome to our video tutorial.",
            middleText: "This technology uses advanced machine learning.",
            endText: "Let's get started with the demonstration.",
          },
          subtitlesPreview: {
            startSubs: [
              "00:00:00 → Welcome to our video tutorial.",
              "00:00:03 → Today we'll be learning about",
            ],
            midSubs: [
              "00:01:45 → This technology uses advanced",
              "00:01:48 → machine learning to convert speech",
            ],
            endSubs: [
              "00:03:22 → Let's get started with the",
              "00:03:25 → demonstration. Thank you!",
            ],
          },
        });
      }, 300);
    });
  }

  // ---------- Working page ----------
  function setupWorking() {
    const supportedFormats = ["mp3", "wav", "mp4", "mov", "webm", "m4a", "ogg"];
    const maxFileSizeMB = 500;

    const state = {
      selectedFile: null,
      language: "auto",
      outputFormat: "srt",
      status: "idle", // idle|uploading|processing|completed|error
      progress: 0,
      result: null,
      errorMessage: null,
      jobId: null,
      pollTimer: null,
      dropError: null,
      copied: false,
    };

    // elements
    const dropzone = $("#dropzone");
    const fileInput = $("#fileInput");
    const dragHint = $("#dragHint");
    const formatsWrap = $("#formatsWrap");
    const maxSizeText = $("#maxSizeText");
    const dropErrorEl = $("#dropError");

    const selectedCard = $("#selectedCard");
    const selectedName = $("#selectedName");
    const selectedSize = $("#selectedSize");
    const selectedIcon = $("#selectedIcon");
    const removeFileBtn = $("#removeFileBtn");

    const optionsWrap = $("#optionsWrap");
    const languageSelect = $("#languageSelect");
    const formatSelect = $("#formatSelect");

    const generateBtn = $("#generateBtn");
    const retryBtn = $("#retryBtn");

    const progressWrap = $("#progressWrap");
    const progressLabel = $("#progressLabel");
    const progressIcon = $("#progressIcon");
    const progressSpinner = $("#progressSpinner");
    const progressBar = $("#progressBar");
    const progressPercent = $("#progressPercent");
    const progressErr = $("#progressErr");

    const resultsWrap = $("#resultsWrap");
    const downloadPrimaryBtn = $("#downloadPrimaryBtn");
    const downloadAltBtn = $("#downloadAltBtn");
    const copyBtn = $("#copyBtn");
    const downloadTxtBtn = $("#downloadTxtBtn");
    const resetBtn = $("#resetBtn");

    const previewWrap = $("#previewWrap");
    const previewStart = $("#previewStart");
    const previewMid = $("#previewMid");
    const previewEnd = $("#previewEnd");

    setText(maxSizeText, `Maximum file size: ${maxFileSizeMB}MB`);
    setHTML(formatsWrap, supportedFormats.map(f => `<span class="px-2 py-1 bg-secondary rounded-md uppercase text-xs text-muted-foreground">${f}</span>`).join(" "));

    function validateFile(file) {
      const ext = (file.name.split(".").pop() || "").toLowerCase();
      if (!ext || !supportedFormats.includes(ext)) {
        state.dropError = `Unsupported format. Please use: ${supportedFormats.join(", ")}`;
        return false;
      }
      if (file.size > maxFileSizeMB * 1024 * 1024) {
        state.dropError = `File too large. Maximum size is ${maxFileSizeMB}MB`;
        return false;
      }
      state.dropError = null;
      return true;
    }

    function setStatus(next, extra) {
      state.status = next;
      if (typeof extra?.progress === "number") state.progress = extra.progress;
      if (typeof extra?.errorMessage === "string") state.errorMessage = extra.errorMessage;
      render();
    }

    function resetAll() {
      state.selectedFile = null;
      state.language = "auto";
      state.outputFormat = "srt";
      state.status = "idle";
      state.progress = 0;
      state.result = null;
      state.errorMessage = null;
      state.jobId = null;
      state.dropError = null;
      state.copied = false;
      if (state.pollTimer) { clearInterval(state.pollTimer); state.pollTimer = null; }
      render();
    }

    function isProcessing() {
      return state.status === "uploading" || state.status === "processing";
    }

    function fileKindIcon(fileName) {
      const ext = (fileName.split(".").pop() || "").toLowerCase();
      if (["mp3", "wav", "m4a", "ogg"].includes(ext)) return "music";
      return "video";
    }

    async function handleGenerate() {
      if (!state.selectedFile || isProcessing()) return;

      try {
        setStatus("uploading", { progress: 0 });
        state.errorMessage = null;

        const res = await uploadFile(state.selectedFile);
        state.jobId = res.jobId;

        // Poll every second
        state.pollTimer = setInterval(async () => {
          try {
            const jobStatus = await getJobStatus(state.jobId);
            if (jobStatus.status === "error") {
              setStatus("error", { errorMessage: jobStatus.error || "An error occurred", progress: 0 });
              clearInterval(state.pollTimer);
              state.pollTimer = null;
              return;
            }

            setStatus(jobStatus.status, { progress: jobStatus.progress });

            if (jobStatus.status === "completed") {
              state.result = await getJobResult(state.jobId);
              clearInterval(state.pollTimer);
              state.pollTimer = null;
              render();
            }
          } catch (e) {
            setStatus("error", { errorMessage: "Failed to check status. Please try again.", progress: 0 });
            clearInterval(state.pollTimer);
            state.pollTimer = null;
          }
        }, 1000);

      } catch (e) {
        setStatus("error", { errorMessage: "Failed to upload file. Please try again.", progress: 0 });
      }
    }

    function renderProgress() {
      if (state.status === "idle") {
        setHidden(progressWrap, true);
        return;
      }

      setHidden(progressWrap, false);

      const cfg = {
        uploading: { label: "Uploading file...", icon: "upload", color: "text-primary" },
        processing: { label: "Generating subtitles...", icon: "wand-2", color: "text-primary" },
        completed: { label: "Subtitles ready!", icon: "check-circle-2", color: "text-accent" },
        error: { label: "Something went wrong", icon: "alert-circle", color: "text-destructive" },
      }[state.status];

      progressLabel.className = `font-medium ${cfg.color}`;
      setText(progressLabel, cfg.label);

      // Spinner for active
      const active = state.status === "uploading" || state.status === "processing";
      setHidden(progressSpinner, !active);
      setHidden(progressIcon, active);

      if (!active) {
        progressIcon.setAttribute("data-lucide", cfg.icon);
        progressIcon.className = cfg.color;
      }

      // Bar and percent
      setHidden(progressBar.parentElement, !active);
      setHidden(progressPercent, !active);

      if (active) {
        progressBar.style.width = `${state.progress}%`;
        setText(progressPercent, `${state.progress}% complete`);
      }

      // Error msg
      setHidden(progressErr, !(state.status === "error" && state.errorMessage));
      if (state.status === "error") setText(progressErr, state.errorMessage || "");
      reIconize();
    }

    function renderPreview() {
      const show = isProcessing() || state.status === "completed";
      setHidden(previewWrap, !show);
      if (!show) return;

      const loading = isProcessing();
      const preview = state.result?.subtitlesPreview;

      function renderLines(container, lines) {
        if (loading) {
          setHTML(container, `
            <div class="space-y-2">
              <div class="h-4 bg-secondary rounded animate-pulse w-[90%]"></div>
              <div class="h-4 bg-secondary rounded animate-pulse w-[75%]"></div>
              <div class="h-4 bg-secondary rounded animate-pulse w-[85%]"></div>
            </div>
          `);
          return;
        }
        if (!lines || !lines.length) {
          setHTML(container, `<p class="text-sm text-muted-foreground italic">No preview available</p>`);
          return;
        }
        setHTML(container, lines.map(l => `<p class="text-sm text-muted-foreground font-mono leading-relaxed">${escapeHtml(l)}</p>`).join(""));
      }

      renderLines(previewStart, preview?.startSubs);
      renderLines(previewMid, preview?.midSubs);
      renderLines(previewEnd, preview?.endSubs);
    }

    function escapeHtml(str) {
      return String(str)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
    }

    function renderResults() {
      const show = state.status === "completed" && !!state.result;
      setHidden(resultsWrap, !show);
      if (!show) return;

      const { srtUrl, vttUrl, transcriptText } = state.result;
      const out = state.outputFormat;

      // Primary
      setText($("#downloadPrimaryLabel"), `Download English Subtitles (.${out.toUpperCase()})`);

      downloadPrimaryBtn.onclick = () => createDownload(out === "srt" ? srtUrl : vttUrl, `subtitles.${out}`);
      downloadAltBtn.onclick = () => createDownload(out === "srt" ? vttUrl : srtUrl, `subtitles.${out === "srt" ? "vtt" : "srt"}`);

      // Copy
      copyBtn.onclick = async () => {
        if (!transcriptText) return;
        try {
          await navigator.clipboard.writeText(transcriptText);
          state.copied = true;
          render(); // update button label
          setTimeout(() => { state.copied = false; render(); }, 2000);
        } catch {
          // ignore
        }
      };

      // TXT
      downloadTxtBtn.onclick = () => {
        if (!transcriptText) return;
        createDownload(`data:text/plain;charset=utf-8,${encodeURIComponent(transcriptText)}`, "transcript.txt");
      };

      setDisabled(copyBtn, !transcriptText);
      setDisabled(downloadTxtBtn, !transcriptText);

      // Button label for copied state
      setText($("#copyLabel"), state.copied ? "Copied!" : "Copy Text");
      $("#copyIcon").setAttribute("data-lucide", state.copied ? "check" : "copy");
      reIconize();
    }

    function render() {
      // Dropzone vs selected file
      const hasFile = !!state.selectedFile;

      setHidden(dropzone, hasFile);
      setHidden(selectedCard, !hasFile);

      // Dropzone disabled styling
      const dropzoneWrap = $("#dropzoneWrap");
      if (dropzoneWrap) {
        dropzoneWrap.classList.toggle("opacity-50", isProcessing());
        dropzoneWrap.classList.toggle("cursor-not-allowed", isProcessing());
      }

      // Drop error
      setHidden(dropErrorEl, !state.dropError);
      if (state.dropError) setText(dropErrorEl, state.dropError);

      // Selected file details
      if (hasFile) {
        setText(selectedName, state.selectedFile.name);
        setText(selectedSize, bytesToSize(state.selectedFile.size));
        selectedIcon.setAttribute("data-lucide", fileKindIcon(state.selectedFile.name));
        reIconize();
      }

      // Options only when file selected and idle
      setHidden(optionsWrap, !(hasFile && state.status === "idle"));

      // Generate button visible only idle
      setHidden(generateBtn, state.status !== "idle");
      setDisabled(generateBtn, !hasFile);

      // Retry only on error
      setHidden(retryBtn, state.status !== "error");

      // Progress
      renderProgress();

      // When completed, hide the whole "input" area and show results
      const inputArea = $("#inputArea");
      setHidden(inputArea, state.status === "completed");

      renderResults();
      renderPreview();

      // Keep selects in sync
      if (languageSelect) languageSelect.value = state.language;
      if (formatSelect) formatSelect.value = state.outputFormat;
    }

    // Dropzone events
    if (fileInput) {
      fileInput.setAttribute("accept", supportedFormats.map(f => "." + f).join(","));
      fileInput.addEventListener("change", (e) => {
        const file = e.target.files && e.target.files[0];
        if (file && validateFile(file)) {
          state.selectedFile = file;
          state.status = "idle";
          state.progress = 0;
          state.result = null;
          state.errorMessage = null;
          state.jobId = null;
          render();
        } else {
          render();
        }
        e.target.value = "";
      });
    }

    function setDragging(isDragging) {
      const dz = $("#dropzoneWrap");
      if (!dz) return;
      dz.classList.toggle("border-primary", isDragging);
      dz.classList.toggle("bg-primary/5", isDragging);
      dz.classList.toggle("scale-[1.02]", isDragging);
      setText(dragHint, isDragging ? "Drop your file here" : "Drag & drop your file");
    }

    if (dropzone) {
      dropzone.addEventListener("dragover", (e) => {
        e.preventDefault();
        if (isProcessing()) return;
        setDragging(true);
      });
      dropzone.addEventListener("dragleave", (e) => {
        e.preventDefault();
        setDragging(false);
      });
      dropzone.addEventListener("drop", (e) => {
        e.preventDefault();
        setDragging(false);
        if (isProcessing()) return;
        const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
        if (file && validateFile(file)) {
          state.selectedFile = file;
          state.status = "idle";
          state.progress = 0;
          state.result = null;
          state.errorMessage = null;
          state.jobId = null;
        }
        render();
      });
    }

    // Remove/reset
    if (removeFileBtn) removeFileBtn.addEventListener("click", () => resetAll());
    if (resetBtn) resetBtn.addEventListener("click", () => resetAll());

    // Selects
    if (languageSelect) languageSelect.addEventListener("change", (e) => { state.language = e.target.value; });
    if (formatSelect) formatSelect.addEventListener("change", (e) => { state.outputFormat = e.target.value; render(); });

    // Buttons
    if (generateBtn) generateBtn.addEventListener("click", handleGenerate);
    if (retryBtn) retryBtn.addEventListener("click", handleGenerate);

    render();
  }

  // ---------- Login page ----------
  function setupLogin() {
    const form = $("#loginForm");
    if (!form) return;

    const email = $("#loginEmail");
    const pass = $("#loginPassword");
    const emailErr = $("#loginEmailErr");
    const passErr = $("#loginPassErr");
    const submitBtn = $("#loginSubmit");
    const toggleBtn = $("#loginTogglePass");

    function validate() {
      let ok = true;
      const valEmail = (email.value || "").trim();
      const valPass = pass.value || "";

      // email
      if (!valEmail) {
        setText(emailErr, "Email is required");
        setHidden(emailErr, false);
        ok = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valEmail)) {
        setText(emailErr, "Please enter a valid email");
        setHidden(emailErr, false);
        ok = false;
      } else {
        setHidden(emailErr, true);
      }

      // pass
      if (!valPass) {
        setText(passErr, "Password is required");
        setHidden(passErr, false);
        ok = false;
      } else {
        setHidden(passErr, true);
      }

      return ok;
    }

    email.addEventListener("input", () => setHidden(emailErr, true));
    pass.addEventListener("input", () => setHidden(passErr, true));

    toggleBtn.addEventListener("click", () => {
      const showing = pass.getAttribute("type") === "text";
      pass.setAttribute("type", showing ? "password" : "text");
      $("#loginEye").setAttribute("data-lucide", showing ? "eye" : "eye-off");
      reIconize();
    });

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (!validate()) return;

      setDisabled(submitBtn, true);
      submitBtn.textContent = "Logging in...";
      await new Promise((r) => setTimeout(r, 1500));
      setDisabled(submitBtn, false);
      submitBtn.textContent = "Log In";
      // Hook your real login here (Laravel)
    });

    reIconize();
  }

  // ---------- Signup page ----------
  function setupSignup() {
    const form = $("#signupForm");
    if (!form) return;

    const nameEl = $("#signupName");
    const email = $("#signupEmail");
    const pass = $("#signupPassword");
    const pass2 = $("#signupConfirmPassword");

    const nameErr = $("#signupNameErr");
    const emailErr = $("#signupEmailErr");
    const passErr = $("#signupPassErr");
    const pass2Err = $("#signupPass2Err");

    const submitBtn = $("#signupSubmit");
    const togglePassBtn = $("#signupTogglePass");
    const togglePass2Btn = $("#signupTogglePass2");

    const strengthBars = $all("[data-strength-bar]");

    function strength(p) {
      if (p.length >= 12) return "strong";
      if (p.length >= 8) return "medium";
      return "weak";
    }

    function renderStrength() {
      const s = strength(pass.value || "");
      if (!pass.value) {
        strengthBars.forEach((b) => b.className = "h-1 flex-1 rounded-full bg-secondary");
        return;
      }
      strengthBars.forEach((b, i) => {
        const on = (s === "strong") || (s === "medium" && i < 2) || i === 0;
        b.className = "h-1 flex-1 rounded-full " + (on ? (s === "strong" ? "bg-accent" : s === "medium" ? "bg-yellow-500" : "bg-destructive") : "bg-secondary");
      });
    }

    function validate() {
      let ok = true;

      if (!(nameEl.value || "").trim()) {
        setText(nameErr, "Name is required");
        setHidden(nameErr, false);
        ok = false;
      } else setHidden(nameErr, true);

      const valEmail = (email.value || "").trim();
      if (!valEmail) {
        setText(emailErr, "Email is required");
        setHidden(emailErr, false);
        ok = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valEmail)) {
        setText(emailErr, "Please enter a valid email");
        setHidden(emailErr, false);
        ok = false;
      } else setHidden(emailErr, true);

      const p = pass.value || "";
      if (!p) {
        setText(passErr, "Password is required");
        setHidden(passErr, false);
        ok = false;
      } else if (p.length < 8) {
        setText(passErr, "Password must be at least 8 characters");
        setHidden(passErr, false);
        ok = false;
      } else setHidden(passErr, true);

      const p2 = pass2.value || "";
      if (!p2) {
        setText(pass2Err, "Please confirm your password");
        setHidden(pass2Err, false);
        ok = false;
      } else if (p !== p2) {
        setText(pass2Err, "Passwords do not match");
        setHidden(pass2Err, false);
        ok = false;
      } else setHidden(pass2Err, true);

      return ok;
    }

    [nameEl, email, pass, pass2].forEach((el) => {
      el.addEventListener("input", () => {
        renderStrength();
        setHidden(nameErr, true);
        setHidden(emailErr, true);
        setHidden(passErr, true);
        setHidden(pass2Err, true);
      });
    });

    togglePassBtn.addEventListener("click", () => {
      const showing = pass.getAttribute("type") === "text";
      pass.setAttribute("type", showing ? "password" : "text");
      $("#signupEye").setAttribute("data-lucide", showing ? "eye" : "eye-off");
      reIconize();
    });

    togglePass2Btn.addEventListener("click", () => {
      const showing = pass2.getAttribute("type") === "text";
      pass2.setAttribute("type", showing ? "password" : "text");
      $("#signupEye2").setAttribute("data-lucide", showing ? "eye" : "eye-off");
      reIconize();
    });

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (!validate()) return;

      setDisabled(submitBtn, true);
      submitBtn.textContent = "Creating account...";
      await new Promise((r) => setTimeout(r, 1500));
      setDisabled(submitBtn, false);
      submitBtn.textContent = "Create Account";
      // Hook your real signup here (Laravel)
    });

    renderStrength();
    reIconize();
  }

  // ---------- boot ----------
  document.addEventListener("DOMContentLoaded", () => {
    setupNavbar();
    setupAnimations();

    const page = document.body.getAttribute("data-page");
    if (page === "working") setupWorking();
    if (page === "login") setupLogin();
    if (page === "signup") setupSignup();

    reIconize();
  });

})();
