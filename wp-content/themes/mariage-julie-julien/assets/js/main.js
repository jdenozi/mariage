/**
 * Mariage Julie & Julien - Main JavaScript
 */
(function () {
    'use strict';

    // ==========================================
    // COUNTDOWN
    // ==========================================
    var countdownEl = document.getElementById('countdown');
    var weddingDateStr = countdownEl ? countdownEl.getAttribute('data-date') : '2027-05-08T14:00:00';
    const weddingDate = new Date(weddingDateStr).getTime();

    function updateCountdown() {
        const now = Date.now();
        const diff = weddingDate - now;

        if (diff <= 0) {
            document.getElementById('countdown-days').textContent = '0';
            document.getElementById('countdown-hours').textContent = '0';
            document.getElementById('countdown-minutes').textContent = '0';
            document.getElementById('countdown-seconds').textContent = '0';
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        document.getElementById('countdown-days').textContent = days;
        document.getElementById('countdown-hours').textContent = hours;
        document.getElementById('countdown-minutes').textContent = minutes;
        document.getElementById('countdown-seconds').textContent = seconds;
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);

    // ==========================================
    // NAVIGATION
    // ==========================================
    const nav = document.getElementById('site-nav');
    const navToggle = document.getElementById('nav-toggle');
    const navLinks = document.getElementById('nav-links');

    // Sticky nav shadow on scroll
    window.addEventListener('scroll', function () {
        nav.classList.toggle('scrolled', window.scrollY > 50);
    });

    // Mobile nav toggle
    if (navToggle) {
        navToggle.addEventListener('click', function () {
            navLinks.classList.toggle('open');
        });
    }

    // Close mobile nav on link click
    document.querySelectorAll('.nav-links a').forEach(function (link) {
        link.addEventListener('click', function () {
            navLinks.classList.remove('open');
        });
    });

    // ==========================================
    // SCROLL ANIMATIONS
    // ==========================================
    const fadeElements = document.querySelectorAll('.fade-in');
    const observer = new IntersectionObserver(
        function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15 }
    );

    fadeElements.forEach(function (el) {
        observer.observe(el);
    });

    // ==========================================
    // RSVP FORM (unifie avec questionnaire)
    // ==========================================
    const rsvpForm = document.getElementById('rsvp-form');
    var rsvpOuiSection = document.getElementById('rsvp-oui-section');
    var rsvpNbInput = document.getElementById('rsvp-nb');
    var rsvpMembres = document.getElementById('rsvp-membres');

    // Show/hide section on presence change
    document.querySelectorAll('#rsvp-form input[name="presence"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (this.value === 'oui') {
                rsvpOuiSection.style.display = 'block';
                rsvpOuiSection.classList.add('slide-down');
                renderMembres(parseInt(rsvpNbInput.value) || 1);
            } else {
                rsvpOuiSection.style.display = 'none';
                rsvpOuiSection.classList.remove('slide-down');
                rsvpMembres.innerHTML = '';
            }
        });
    });

    function renderMembres(nb) {
        rsvpMembres.innerHTML = '';
        for (var i = 1; i <= nb; i++) {
            var idx = i - 1;
            var div = document.createElement('div');
            div.className = 'slide-down';
            div.innerHTML =
                '<div class="form-divider"><span>Personne ' + i + '</span></div>' +
                '<div class="form-group">' +
                '  <label for="rsvp-membre-' + i + '-nom">Nom et prenom *</label>' +
                '  <input type="text" id="rsvp-membre-' + i + '-nom" name="membres[' + idx + '][nom]" placeholder="Nom complet" required>' +
                '</div>' +
                '<div class="form-group">' +
                '  <label>Allergies ou intolerances alimentaires ?</label>' +
                '  <div class="radio-group">' +
                '    <label class="radio-option"><input type="radio" name="membres[' + idx + '][allergies]" value="non" checked><span>Non</span></label>' +
                '    <label class="radio-option"><input type="radio" name="membres[' + idx + '][allergies]" value="oui"><span>Oui</span></label>' +
                '  </div>' +
                '</div>' +
                '<div class="form-group" id="rsvp-membre-' + i + '-allergies-detail" style="display:none;">' +
                '  <label for="rsvp-membre-' + i + '-allergies-texte">Precisez les allergies / intolerances</label>' +
                '  <textarea id="rsvp-membre-' + i + '-allergies-texte" name="membres[' + idx + '][texte_allergies]" placeholder="Ex: gluten, lactose, fruits a coque..."></textarea>' +
                '</div>';
            rsvpMembres.appendChild(div);

            // Bind allergy toggle
            (function (personNum, memberIdx, element) {
                element.querySelectorAll('input[name="membres[' + memberIdx + '][allergies]"]').forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        var detail = document.getElementById('rsvp-membre-' + personNum + '-allergies-detail');
                        if (this.value === 'oui') {
                            detail.style.display = 'block';
                            detail.classList.add('slide-down');
                        } else {
                            detail.style.display = 'none';
                            detail.classList.remove('slide-down');
                        }
                    });
                });
            })(i, idx, div);
        }
    }

    if (rsvpNbInput) {
        rsvpNbInput.addEventListener('change', function () {
            renderMembres(parseInt(this.value) || 1);
        });
    }

    // Show/hide enfants detail
    document.querySelectorAll('#rsvp-form input[name="enfants"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            var detail = document.getElementById('rsvp-enfants-detail');
            if (this.value === 'oui') {
                detail.style.display = 'block';
                detail.classList.add('slide-down');
            } else {
                detail.style.display = 'none';
                detail.classList.remove('slide-down');
            }
        });
    });

    if (rsvpForm) {
        rsvpForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitForm(rsvpForm, 'mariage_rsvp', 'rsvp-message');
        });
    }

    // ==========================================
    // GENERIC FORM SUBMIT (AJAX)
    // ==========================================
    function submitForm(form, action, messageId) {
        var btn = form.querySelector('button[type="submit"]');
        var msgEl = document.getElementById(messageId);

        btn.classList.add('loading');
        btn.disabled = true;
        msgEl.className = 'form-message';
        msgEl.style.display = 'none';

        var formData = new FormData(form);
        formData.append('action', action);
        formData.append('nonce', mariageAjax.nonce);

        fetch(mariageAjax.ajaxurl, {
            method: 'POST',
            body: formData,
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                btn.classList.remove('loading');
                btn.disabled = false;

                if (data.success) {
                    msgEl.className = 'form-message success';
                    msgEl.textContent = data.data.message;
                    msgEl.style.display = 'block';
                    form.reset();
                    // Hide conditional fields
                    if (rsvpOuiSection) rsvpOuiSection.style.display = 'none';
                    if (rsvpMembres) rsvpMembres.innerHTML = '';
                    var enfantsDetail = document.getElementById('rsvp-enfants-detail');
                    if (enfantsDetail) enfantsDetail.style.display = 'none';
                } else {
                    msgEl.className = 'form-message error';
                    msgEl.textContent = data.data.message;
                    msgEl.style.display = 'block';
                }

                // Scroll to message
                msgEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            })
            .catch(function () {
                btn.classList.remove('loading');
                btn.disabled = false;
                msgEl.className = 'form-message error';
                msgEl.textContent = 'Erreur de connexion. Veuillez reessayer.';
                msgEl.style.display = 'block';
            });
    }

    // ==========================================
    // PHOTOS UPLOAD
    // ==========================================
    var uploadArea = document.getElementById('upload-area');
    var photoInput = document.getElementById('photo-input');
    var uploadForm = document.getElementById('photos-upload-form');
    var uploadProgress = document.getElementById('upload-progress');
    var progressBarFill = document.getElementById('progress-bar-fill');
    var uploadStatus = document.getElementById('upload-status');
    var uploadBtn = document.getElementById('upload-btn');

    if (uploadArea && photoInput) {
        // Click to select files
        uploadArea.addEventListener('click', function () {
            photoInput.click();
        });

        // Show selected files
        photoInput.addEventListener('change', function () {
            showFilePreviews(this.files);
        });

        // Drag & drop
        uploadArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function () {
            this.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('dragover');
            photoInput.files = e.dataTransfer.files;
            showFilePreviews(e.dataTransfer.files);
        });
    }

    function showFilePreviews(files) {
        var existing = document.querySelector('.file-previews');
        if (existing) existing.remove();

        if (!files || files.length === 0) return;

        var container = document.createElement('div');
        container.className = 'file-previews';

        Array.from(files).forEach(function (file) {
            var preview = document.createElement('div');
            preview.className = 'file-preview';

            if (file.type.startsWith('image/')) {
                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                preview.appendChild(img);
            } else {
                var label = document.createElement('div');
                label.className = 'file-type-label';
                label.textContent = file.type.split('/')[1];
                label.style.position = 'static';
                label.style.height = '100%';
                label.style.display = 'flex';
                label.style.alignItems = 'center';
                label.style.justifyContent = 'center';
                label.style.fontSize = '0.8rem';
                preview.appendChild(label);
            }

            container.appendChild(preview);
        });

        uploadArea.parentNode.insertBefore(container, uploadArea.nextSibling);
    }

    if (uploadForm) {
        uploadForm.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!photoInput.files || photoInput.files.length === 0) {
                var msg = document.getElementById('photos-message');
                msg.className = 'form-message error';
                msg.textContent = 'Veuillez selectionner au moins un fichier.';
                msg.style.display = 'block';
                return;
            }

            uploadBtn.classList.add('loading');
            uploadBtn.disabled = true;
            uploadProgress.style.display = 'block';
            progressBarFill.style.width = '0%';

            var formData = new FormData();
            formData.append('action', 'mariage_upload_photos');
            formData.append('nonce', mariageAjax.nonce);
            formData.append('nom_invite', document.getElementById('photo-nom').value);

            Array.from(photoInput.files).forEach(function (file) {
                formData.append('photos[]', file);
            });

            var xhr = new XMLHttpRequest();
            xhr.open('POST', mariageAjax.ajaxurl, true);

            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    var percent = Math.round((e.loaded / e.total) * 100);
                    progressBarFill.style.width = percent + '%';
                    uploadStatus.textContent = 'Envoi en cours... ' + percent + '%';
                }
            });

            xhr.addEventListener('load', function () {
                uploadBtn.classList.remove('loading');
                uploadBtn.disabled = false;
                uploadProgress.style.display = 'none';

                var msgEl = document.getElementById('photos-message');
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        msgEl.className = 'form-message success';
                        msgEl.textContent = data.data.message;
                        uploadForm.reset();
                        var previews = document.querySelector('.file-previews');
                        if (previews) previews.remove();
                        refreshGallery();
                    } else {
                        msgEl.className = 'form-message error';
                        msgEl.textContent = data.data.message;
                    }
                } catch (err) {
                    msgEl.className = 'form-message error';
                    msgEl.textContent = 'Erreur lors de l\'envoi.';
                }
                msgEl.style.display = 'block';
            });

            xhr.addEventListener('error', function () {
                uploadBtn.classList.remove('loading');
                uploadBtn.disabled = false;
                uploadProgress.style.display = 'none';
                var msgEl = document.getElementById('photos-message');
                msgEl.className = 'form-message error';
                msgEl.textContent = 'Erreur de connexion.';
                msgEl.style.display = 'block';
            });

            xhr.send(formData);
        });
    }

    // ==========================================
    // CAGNOTTE PROGRESS BAR
    // ==========================================
    function loadCagnotte() {
        var formData = new FormData();
        formData.append('action', 'get_cagnotte');
        formData.append('nonce', mariageAjax.nonce);

        fetch(mariageAjax.ajaxurl, {
            method: 'POST',
            body: formData,
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) return;

                var collected = data.data.collected;
                var goal = data.data.goal;
                var percent = Math.min(Math.round((collected / goal) * 100), 100);

                var amountEl = document.getElementById('cagnotte-amount');
                var fillEl = document.getElementById('cagnotte-fill');
                var percentEl = document.getElementById('cagnotte-percent');

                if (amountEl) amountEl.textContent = collected.toLocaleString('fr-FR') + ' €';
                if (fillEl) fillEl.style.width = percent + '%';
                if (percentEl) percentEl.textContent = percent + '% de l\'objectif atteint';
            })
            .catch(function () {
                var amountEl = document.getElementById('cagnotte-amount');
                if (amountEl) amountEl.textContent = '—';
            });
    }

    loadCagnotte();

    function refreshGallery() {
        var formData = new FormData();
        formData.append('action', 'mariage_get_photos');
        formData.append('nonce', mariageAjax.nonce);

        fetch(mariageAjax.ajaxurl, {
            method: 'POST',
            body: formData,
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) return;
                var gallery = document.getElementById('photos-gallery');
                gallery.innerHTML = '';

                data.data.photos.forEach(function (photo) {
                    var item = document.createElement('div');
                    item.className = 'gallery-item';

                    var isVideo = photo.file_type && photo.file_type.indexOf('video') !== -1;
                    if (isVideo) {
                        item.innerHTML = '<video src="' + photo.file_url + '" controls></video>';
                    } else {
                        item.innerHTML = '<img src="' + photo.file_url + '" alt="Photo">';
                    }

                    if (photo.nom_invite) {
                        item.innerHTML += '<div class="photo-author">' + photo.nom_invite + '</div>';
                    }

                    gallery.appendChild(item);
                });
            });
    }
})();
