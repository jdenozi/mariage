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
            const [isDragging, setIsDragging] = useState(false);
            const containerRef = useRef(null);
            const imageRef = useRef(null);

            const blockProps = useBlockProps({
                className: 'decoration-image-editor',
                style: {
                    position: 'relative',
                    minHeight: '100px',
                    border: '2px dashed #ccc',
                    borderRadius: '8px',
                    marginBottom: '20px',
                    background: '#f9f9f9',
                }
            });

            useEffect(function() {
                if (!isDragging) return;

                var handleMouseMove = function(e) {
                    if (!containerRef.current) return;

                    var rect = containerRef.current.getBoundingClientRect();
                    var x = ((e.clientX - rect.left) / rect.width) * 100;
                    var y = ((e.clientY - rect.top) / rect.height) * 100;

                    setAttributes({
                        positionX: Math.max(0, Math.min(100, Math.round(x))),
                        positionY: Math.max(0, Math.min(100, Math.round(y)))
                    });
                };

                var handleMouseUp = function() {
                    setIsDragging(false);
                };

                document.addEventListener('mousemove', handleMouseMove);
                document.addEventListener('mouseup', handleMouseUp);

                return function() {
                    document.removeEventListener('mousemove', handleMouseMove);
                    document.removeEventListener('mouseup', handleMouseUp);
                };
            }, [isDragging]);

            var handleMouseDown = function(e) {
                e.preventDefault();
                setIsDragging(true);
            };

            var imageStyle = {
                position: 'absolute',
                left: positionX + '%',
                top: positionY + '%',
                width: size + 'vw',
                maxWidth: '80%',
                height: 'auto',
                opacity: opacity / 100,
                cursor: isDragging ? 'grabbing' : 'grab',
                transform: flipHorizontal ? 'scaleX(-1)' : 'none',
                pointerEvents: 'all',
                zIndex: 10,
                userSelect: 'none',
                border: isDragging ? '2px solid #007cba' : '2px solid transparent',
                borderRadius: '4px',
            };

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
                wp.element.createElement(
                    'div',
                    {
                        ref: containerRef,
                        style: {
                            position: 'relative',
                            width: '100%',
                            height: '250px',
                            overflow: 'hidden',
                            background: '#e8e8e8',
                            backgroundImage: 'linear-gradient(45deg, #d0d0d0 25%, transparent 25%), linear-gradient(-45deg, #d0d0d0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #d0d0d0 75%), linear-gradient(-45deg, transparent 75%, #d0d0d0 75%)',
                            backgroundSize: '20px 20px',
                            backgroundPosition: '0 0, 0 10px, 10px -10px, -10px 0px',
                            borderRadius: '4px',
                        }
                    },
                    !imageUrl && wp.element.createElement(
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
                                        {
                                            onClick: renderProps.open,
                                            variant: 'primary',
                                            style: {
                                                position: 'absolute',
                                                top: '50%',
                                                left: '50%',
                                                transform: 'translate(-50%, -50%)'
                                            }
                                        },
                                        'Choisir une image decorative'
                                    );
                                }
                            }
                        )
                    ),
                    imageUrl && wp.element.createElement(
                        'img',
                        {
                            ref: imageRef,
                            src: imageUrl,
                            alt: '',
                            style: imageStyle,
                            onMouseDown: handleMouseDown,
                            draggable: false
                        }
                    ),
                    imageUrl && wp.element.createElement(
                        'div',
                        {
                            style: {
                                position: 'absolute',
                                bottom: '8px',
                                right: '8px',
                                display: 'flex',
                                gap: '5px'
                            }
                        },
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
                                            { onClick: renderProps.open, variant: 'secondary', isSmall: true },
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
                                isSmall: true,
                                isDestructive: true
                            },
                            'Supprimer'
                        )
                    ),
                    imageUrl && wp.element.createElement(
                        'div',
                        {
                            style: {
                                position: 'absolute',
                                top: '8px',
                                left: '8px',
                                padding: '4px 10px',
                                background: 'rgba(0,0,0,0.7)',
                                color: 'white',
                                borderRadius: '4px',
                                fontSize: '12px',
                                fontWeight: '500'
                            }
                        },
                        isDragging ? 'Relacher pour placer' : 'Cliquer et glisser pour deplacer'
                    ),
                    imageUrl && wp.element.createElement(
                        'div',
                        {
                            style: {
                                position: 'absolute',
                                top: '8px',
                                right: '8px',
                                padding: '4px 10px',
                                background: 'rgba(0,120,180,0.9)',
                                color: 'white',
                                borderRadius: '4px',
                                fontSize: '11px'
                            }
                        },
                        'X: ' + positionX + '% | Y: ' + positionY + '%'
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
