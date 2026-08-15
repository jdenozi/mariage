(function (wp) {
    if (!wp || !wp.blocks) {
        console.error('wp.blocks not available');
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { useBlockProps, MediaUpload, MediaUploadCheck, InspectorControls } = wp.blockEditor;
    const { PanelBody, RangeControl, Button, ToggleControl } = wp.components;
    const { useState, useRef, useEffect } = wp.element;

    registerBlockType('mariage/decoration-image', {
        apiVersion: 2,
        title: 'Image decorative',
        description: 'Ajouter une image decorative positionnee librement sur la page',
        icon: 'format-image',
        category: 'design',
        keywords: ['decoration', 'image', 'fleur', 'background', 'fond'],
        attributes: {
            imageUrl: { type: 'string', default: '' },
            imageId: { type: 'number', default: 0 },
            positionX: { type: 'number', default: 10 },
            positionY: { type: 'number', default: 10 },
            size: { type: 'number', default: 15 },
            opacity: { type: 'number', default: 100 },
            zIndex: { type: 'number', default: 1 },
            hideOnMobile: { type: 'boolean', default: false },
            flipHorizontal: { type: 'boolean', default: false },
        },

        edit: function (props) {
            const { attributes, setAttributes } = props;
            const { imageUrl, positionX, positionY, size, opacity, zIndex, hideOnMobile, flipHorizontal } = attributes;
            const [isPositioning, setIsPositioning] = useState(false);

            const blockProps = useBlockProps({
                className: 'decoration-image-editor',
                style: {
                    position: 'relative',
                    padding: '15px',
                    border: '2px dashed #ccc',
                    borderRadius: '8px',
                    marginBottom: '20px',
                    background: '#f9f9f9',
                }
            });

            // Full-page positioning mode
            useEffect(function() {
                if (!isPositioning || !imageUrl) return;

                var overlay = document.createElement('div');
                overlay.id = 'deco-position-overlay';
                overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:999999;background:rgba(0,0,0,0.4);cursor:crosshair;';

                var img = document.createElement('img');
                img.src = imageUrl;
                img.style.cssText = 'position:fixed;left:' + positionX + '%;top:' + positionY + '%;width:' + size + 'vw;height:auto;opacity:' + (opacity/100) + ';pointer-events:none;z-index:1000000;transform:' + (flipHorizontal ? 'scaleX(-1)' : 'none') + ';transition:none;';
                img.id = 'deco-position-img';

                var instructions = document.createElement('div');
                instructions.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);background:#1e1e1e;color:#fff;padding:15px 30px;border-radius:8px;font-size:14px;z-index:1000001;text-align:center;font-family:sans-serif;box-shadow:0 4px 20px rgba(0,0,0,0.3);';
                instructions.innerHTML = '<strong style="font-size:16px;">Mode positionnement</strong><br><span style="opacity:0.8;">Cliquez pour placer l\'image</span><br><small style="opacity:0.6;">Echap ou clic droit pour annuler</small>';

                var coords = document.createElement('div');
                coords.id = 'deco-coords';
                coords.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:rgba(0,120,180,0.95);color:#fff;padding:12px 25px;border-radius:8px;font-size:18px;z-index:1000001;font-family:monospace;box-shadow:0 4px 15px rgba(0,0,0,0.2);';
                coords.textContent = 'X: ' + positionX + '% | Y: ' + positionY + '%';

                overlay.appendChild(img);
                overlay.appendChild(instructions);
                overlay.appendChild(coords);
                document.body.appendChild(overlay);

                var currentX = positionX;
                var currentY = positionY;

                var handleMouseMove = function(e) {
                    var x = (e.clientX / window.innerWidth) * 100;
                    var y = (e.clientY / window.innerHeight) * 100;
                    currentX = Math.max(0, Math.min(100, Math.round(x)));
                    currentY = Math.max(0, Math.min(100, Math.round(y)));

                    img.style.left = currentX + '%';
                    img.style.top = currentY + '%';
                    coords.textContent = 'X: ' + currentX + '% | Y: ' + currentY + '%';
                };

                var handleClick = function(e) {
                    if (e.button === 2) return;
                    e.preventDefault();
                    setAttributes({ positionX: currentX, positionY: currentY });
                    cleanup();
                };

                var handleKeyDown = function(e) {
                    if (e.key === 'Escape') {
                        cleanup();
                    }
                };

                var handleContextMenu = function(e) {
                    e.preventDefault();
                    cleanup();
                };

                var cleanup = function() {
                    if (overlay && overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                    document.removeEventListener('keydown', handleKeyDown);
                    setIsPositioning(false);
                };

                overlay.addEventListener('mousemove', handleMouseMove);
                overlay.addEventListener('click', handleClick);
                overlay.addEventListener('contextmenu', handleContextMenu);
                document.addEventListener('keydown', handleKeyDown);

                return function() {
                    if (overlay && overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                    document.removeEventListener('keydown', handleKeyDown);
                };
            }, [isPositioning, imageUrl, size, opacity, flipHorizontal]);

            return wp.element.createElement(
                'div',
                blockProps,
                wp.element.createElement(
                    InspectorControls,
                    null,
                    wp.element.createElement(
                        PanelBody,
                        { title: 'Position', initialOpen: true },
                        wp.element.createElement(RangeControl, {
                            label: 'Position horizontale (%)',
                            value: positionX,
                            onChange: function(val) { setAttributes({ positionX: val }); },
                            min: 0,
                            max: 100
                        }),
                        wp.element.createElement(RangeControl, {
                            label: 'Position verticale (%)',
                            value: positionY,
                            onChange: function(val) { setAttributes({ positionY: val }); },
                            min: 0,
                            max: 100
                        })
                    ),
                    wp.element.createElement(
                        PanelBody,
                        { title: 'Apparence', initialOpen: true },
                        wp.element.createElement(RangeControl, {
                            label: 'Taille (% largeur ecran)',
                            value: size,
                            onChange: function(val) { setAttributes({ size: val }); },
                            min: 5,
                            max: 50
                        }),
                        wp.element.createElement(RangeControl, {
                            label: 'Opacite (%)',
                            value: opacity,
                            onChange: function(val) { setAttributes({ opacity: val }); },
                            min: 10,
                            max: 100
                        }),
                        wp.element.createElement(RangeControl, {
                            label: 'Z-index (superposition)',
                            value: zIndex,
                            onChange: function(val) { setAttributes({ zIndex: val }); },
                            min: -10,
                            max: 100
                        }),
                        wp.element.createElement(ToggleControl, {
                            label: 'Retourner horizontalement',
                            checked: flipHorizontal,
                            onChange: function(val) { setAttributes({ flipHorizontal: val }); }
                        }),
                        wp.element.createElement(ToggleControl, {
                            label: 'Masquer sur mobile',
                            checked: hideOnMobile,
                            onChange: function(val) { setAttributes({ hideOnMobile: val }); }
                        })
                    )
                ),
                !imageUrl && wp.element.createElement(
                    'div',
                    { style: { textAlign: 'center', padding: '30px' } },
                    wp.element.createElement(
                        MediaUploadCheck,
                        null,
                        wp.element.createElement(
                            MediaUpload,
                            {
                                onSelect: function(media) { setAttributes({ imageUrl: media.url, imageId: media.id }); },
                                allowedTypes: ['image'],
                                render: function(renderProps) {
                                    return wp.element.createElement(
                                        Button,
                                        { onClick: renderProps.open, variant: 'primary', style: { padding: '15px 30px', fontSize: '14px' } },
                                        '+ Choisir une image decorative'
                                    );
                                }
                            }
                        )
                    )
                ),
                imageUrl && wp.element.createElement(
                    'div',
                    { style: { display: 'flex', alignItems: 'center', gap: '15px' } },
                    wp.element.createElement(
                        'img',
                        {
                            src: imageUrl,
                            alt: '',
                            style: {
                                width: '80px',
                                height: '80px',
                                objectFit: 'contain',
                                borderRadius: '8px',
                                border: '2px solid #ddd',
                                background: '#fff',
                                transform: flipHorizontal ? 'scaleX(-1)' : 'none',
                            }
                        }
                    ),
                    wp.element.createElement(
                        'div',
                        { style: { flex: 1 } },
                        wp.element.createElement(
                            'div',
                            { style: { marginBottom: '10px', fontSize: '13px', color: '#555' } },
                            'Position: ', wp.element.createElement('strong', null, positionX + '%, ' + positionY + '%'),
                            ' | Taille: ', wp.element.createElement('strong', null, size + 'vw'),
                            hideOnMobile ? ' | Mobile: masque' : ''
                        ),
                        wp.element.createElement(
                            'div',
                            { style: { display: 'flex', gap: '8px', flexWrap: 'wrap' } },
                            wp.element.createElement(
                                Button,
                                {
                                    onClick: function() { setIsPositioning(true); },
                                    variant: 'primary'
                                },
                                'Positionner sur la page'
                            ),
                            wp.element.createElement(
                                MediaUploadCheck,
                                null,
                                wp.element.createElement(
                                    MediaUpload,
                                    {
                                        onSelect: function(media) { setAttributes({ imageUrl: media.url, imageId: media.id }); },
                                        allowedTypes: ['image'],
                                        render: function(renderProps) {
                                            return wp.element.createElement(
                                                Button,
                                                { onClick: renderProps.open, variant: 'secondary' },
                                                'Changer'
                                            );
                                        }
                                    }
                                )
                            ),
                            wp.element.createElement(
                                Button,
                                {
                                    onClick: function() { setAttributes({ imageUrl: '', imageId: 0 }); },
                                    variant: 'secondary',
                                    isDestructive: true
                                },
                                'Supprimer'
                            )
                        )
                    )
                )
            );
        },

        save: function (props) {
            var attr = props.attributes;

            if (!attr.imageUrl) return null;

            var className = 'decoration-image' + (attr.hideOnMobile ? ' hide-on-mobile' : '');

            var style = {
                position: 'fixed',
                left: attr.positionX + '%',
                top: attr.positionY + '%',
                width: attr.size + 'vw',
                height: 'auto',
                opacity: attr.opacity / 100,
                zIndex: attr.zIndex,
                pointerEvents: 'none',
                transform: attr.flipHorizontal ? 'scaleX(-1)' : 'none',
            };

            return wp.element.createElement(
                'img',
                {
                    src: attr.imageUrl,
                    alt: '',
                    className: className,
                    style: style,
                    'aria-hidden': 'true'
                }
            );
        }
    });
})(window.wp);
