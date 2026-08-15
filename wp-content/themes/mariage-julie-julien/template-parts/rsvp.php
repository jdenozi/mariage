<?php
$title        = get_theme_mod('rsvp_title', 'Confirmez votre presence');
$subtitle     = get_theme_mod('rsvp_subtitle', 'Merci de nous confirmer votre venue avant le 1er avril 2027');
$btn_text     = get_theme_mod('rsvp_btn_text', 'Envoyer ma reponse');
$note_enfants = get_theme_mod('questionnaire_note_enfants', "Nous avons fait le choix d'un mariage sans enfant afin que chacun puisse profiter pleinement de la fete. Si vous n'avez vraiment pas d'autre solution, merci de nous en informer ci-dessous.");
?>
<section class="section" id="rsvp">
    <canvas class="section-canvas" id="canvas-rsvp"></canvas>
    <div class="section-container">
        <div class="section-header fade-in">
            <?php get_template_part('template-parts/floral-frame'); ?>
            <h2 class="section-title"><?php echo esc_html($title); ?></h2>
            <p class="section-subtitle"><?php echo esc_html($subtitle); ?></p>
        </div>

        <form id="rsvp-form" class="fade-in" novalidate>
            <div class="form-group">
                <label for="rsvp-email">Email *</label>
                <input type="email" id="rsvp-email" name="email" required placeholder="votre@email.com">
            </div>

            <div class="form-group">
                <label>Serez-vous present(e) ? *</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="presence" value="oui" required>
                        <span>Oui, avec joie !</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="presence" value="non">
                        <span>Non, malheureusement</span>
                    </label>
                </div>
            </div>

            <!-- Section visible si present -->
            <div id="rsvp-oui-section" style="display:none;">
                <div class="form-group">
                    <label for="rsvp-nb">Nombre de personnes *</label>
                    <input type="number" id="rsvp-nb" name="nb_personnes" min="1" max="15" value="1">
                </div>

                <!-- Noms + allergies de chaque personne -->
                <div id="rsvp-membres"></div>

                <!-- Enfants -->
                <div class="form-group">
                    <label>Viendrez-vous avec des enfants ?</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="enfants" value="non" checked>
                            <span>Non</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="enfants" value="oui">
                            <span>Oui</span>
                        </label>
                    </div>
                    <p class="form-note"><?php echo esc_html($note_enfants); ?></p>
                </div>

                <div id="rsvp-enfants-detail" style="display:none;">
                    <div class="form-group">
                        <label for="rsvp-nb-enfants">Nombre d'enfants</label>
                        <input type="number" id="rsvp-nb-enfants" name="nb_enfants" min="1" max="10" value="1">
                    </div>
                    <div class="form-group">
                        <label for="rsvp-enfants-raison">Merci de nous expliquer votre situation</label>
                        <textarea id="rsvp-enfants-raison" name="enfants_raison" placeholder="Ex: bebe allaite, pas de mode de garde possible ce week-end..."></textarea>
                        <p class="form-note">Nous comprenons que certaines situations sont compliquees. Nous reviendrons vers vous pour en discuter.</p>
                    </div>
                </div>

                <!-- Transport -->
                <div class="form-group">
                    <label>Comment viendrez-vous ?</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="transport" value="voiture" checked>
                            <span>En voiture</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="transport" value="tram">
                            <span>En tram</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="transport" value="pied">
                            <span>A pied</span>
                        </label>
                    </div>
                    <p class="form-note">15 places de parking sont disponibles sur le domaine. Les premiers a repondre auront la priorite !</p>
                </div>

                <!-- Discours -->
                <div class="form-group">
                    <label>Souhaitez-vous faire un discours ?</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="discours" value="non" checked>
                            <span>Non</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="discours" value="oui">
                            <span>Oui !</span>
                        </label>
                    </div>
                </div>

                <!-- Animation -->
                <div class="form-group">
                    <label>Souhaitez-vous proposer une animation ?</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="animation" value="non" checked>
                            <span>Non</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="animation" value="oui">
                            <span>Oui !</span>
                        </label>
                    </div>
                    <p class="form-note">Jeu, sketch, chanson, danse, quiz... Toute idee est la bienvenue !</p>
                </div>

                <div class="form-group" id="rsvp-animation-detail" style="display:none;">
                    <label for="rsvp-animation-desc">Decrivez brievement votre idee</label>
                    <textarea id="rsvp-animation-desc" name="animation_description" placeholder="Ex: un quiz sur les maries, une chanson revisitee..."></textarea>
                </div>
            </div>

            <!-- Commentaire (toujours visible) -->
            <div class="form-group">
                <label for="rsvp-commentaire">Un petit mot pour les maries ? (facultatif)</label>
                <textarea id="rsvp-commentaire" name="commentaire" placeholder="Votre message..."></textarea>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary"><?php echo esc_html($btn_text); ?></button>
            </div>

            <div id="rsvp-message" class="form-message"></div>
        </form>
    </div>
</section>
