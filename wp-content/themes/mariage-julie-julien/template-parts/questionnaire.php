<?php
$title         = get_theme_mod('questionnaire_title', 'Quelques questions');
$subtitle      = get_theme_mod('questionnaire_subtitle', 'Pour que cette journee soit parfaite pour tout le monde');
$btn_text      = get_theme_mod('questionnaire_btn_text', 'Envoyer mes reponses');
$note_enfants  = get_theme_mod('questionnaire_note_enfants', "Nous preferons que cette journee soit une occasion pour les adultes de profiter pleinement de la fete. Si vous le pouvez, nous vous encourageons a faire garder vos enfants pour cette soiree. Merci de votre comprehension !");
?>
<section class="section section--alt" id="questionnaire">
    <canvas class="section-canvas" id="canvas-questionnaire"></canvas>
    <div class="section-container">
        <div class="section-header fade-in">
            <?php get_template_part('template-parts/floral-frame'); ?>
            <h2 class="section-title"><?php echo esc_html($title); ?></h2>
            <p class="section-subtitle"><?php echo esc_html($subtitle); ?></p>
        </div>

        <form id="questionnaire-form" class="fade-in" novalidate>
            <!-- Nombre de personnes -->
            <div class="form-group">
                <label for="q-nb-personnes">Nombre de personnes dans votre groupe *</label>
                <input type="number" id="q-nb-personnes" name="nb_personnes" min="1" max="15" value="1">
            </div>

            <!-- Personne 1 -->
            <div class="form-group">
                <label for="q-nom">Votre nom et prenom *</label>
                <input type="text" id="q-nom" name="nom" required placeholder="Votre nom complet">
            </div>

            <div class="form-group">
                <label>Avez-vous des allergies ou intolerances alimentaires ?</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="allergies" value="non" checked>
                        <span>Non</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="allergies" value="oui">
                        <span>Oui</span>
                    </label>
                </div>
            </div>

            <div class="form-group" id="q-allergies-detail" style="display:none;">
                <label for="q-allergies-texte">Precisez vos allergies / intolerances</label>
                <textarea id="q-allergies-texte" name="texte_allergies" placeholder="Ex: gluten, lactose, fruits a coque..."></textarea>
            </div>

            <!-- Membres supplementaires du groupe -->
            <div id="q-membres"></div>

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

            <div class="form-group" id="q-enfants-detail" style="display:none;">
                <label for="q-nb-enfants">Nombre d'enfants</label>
                <input type="number" id="q-nb-enfants" name="nb_enfants" min="1" max="10" value="1">
            </div>

            <!-- Discours -->
            <div class="form-group">
                <label>Souhaitez-vous faire un discours ou une animation ?</label>
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

            <!-- Commentaire -->
            <div class="form-group">
                <label for="q-commentaire">Un petit mot pour les maries ? (facultatif)</label>
                <textarea id="q-commentaire" name="commentaire" placeholder="Votre message..."></textarea>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary"><?php echo esc_html($btn_text); ?></button>
            </div>

            <div id="questionnaire-message" class="form-message"></div>
        </form>
    </div>
</section>
