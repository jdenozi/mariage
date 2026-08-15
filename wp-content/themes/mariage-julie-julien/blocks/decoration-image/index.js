(function (wp) {
    if (!wp || !wp.blocks) {
        console.error('wp.blocks not available');
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { useBlockProps, MediaUpload, MediaUploadCheck, InspectorControls } = wp.blockEditor;
    const { PanelBody, RangeControl, Button, ToggleControl } = wp.components;
    const { useState, useRef } = wp.element;

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

            const handleDragStart = (e) => {
                setIsDragging(true);
                e.dataTransfer.setDragImage(new Image(), 0, 0);
            };

            const handleDrag = (e) => {
                if (!isDragging || !containerRef.current || e.clientX === 0) return;

                const rect = containerRef.current.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;

                setAttributes({
                    positionX: Math.max(0, Math.min(100, Math.round(x))),
                    positionY: Math.max(0, Math.min(100, Math.round(y)))
                });
            };

            const handleDragEnd = () => {
                setIsDragging(false);
            };

            const imageStyle = {
                position: 'absolute',
                left: positionX + '%',
                top: positionY + '%',
                width: size + 'vw',
                height: 'auto',
                opacity: opacity / 100,
                cursor: 'move',
                transform: flipHorizontal ? 'scaleX(-1)' : 'none',
                pointerEvents: 'all',
                zIndex: 10,
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
                            onChange: (val) => setAttributes({ positionX: val }),
                            min: 0,
                            max: 100
                        }),
                        wp.element.createElement(RangeControl, {
                            label: 'Position verticale (%)',
                            value: positionY,
                            onChange: (val) => setAttributes({ positionY: val }),
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
                            onChange: (val) => setAttributes({ size: val }),
                            min: 5,
                            max: 50
                        }),
                        wp.element.createElement(RangeControl, {
                            label: 'Opacite (%)',
                            value: opacity,
                            onChange: (val) => setAttributes({ opacity: val }),
                            min: 10,
                            max: 100
                        }),
                        wp.element.createElement(RangeControl, {
                            label: 'Z-index (superposition)',
                            value: zIndex,
                            onChange: (val) => setAttributes({ zIndex: val }),
                            min: -10,
                            max: 100
                        }),
                        wp.element.createElement(ToggleControl, {
                            label: 'Retourner horizontalement',
                            checked: flipHorizontal,
                            onChange: (val) => setAttributes({ flipHorizontal: val })
                        }),
                        wp.element.createElement(ToggleControl, {
                            label: 'Masquer sur mobile',
                            checked: hideOnMobile,
                            onChange: (val) => setAttributes({ hideOnMobile: val })
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
                            height: '200px',
                            overflow: 'visible',
                            background: 'linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%)',
                            backgroundSize: '20px 20px',
                            backgroundPosition: '0 0, 0 10px, 10px -10px, -10px 0px',
                            borderRadius: '4px',
                        },
                        onDragOver: (e) => e.preventDefault(),
                    },
                    !imageUrl && wp.element.createElement(
                        MediaUploadCheck,
                        null,
                        wp.element.createElement(
                            MediaUpload,
                            {
                                onSelect: (media) => setAttributes({ imageUrl: media.url, imageId: media.id }),
                                allowedTypes: ['image'],
                                render: ({ open }) => wp.element.createElement(
                                    Button,
                                    {
                                        onClick: open,
                                        variant: 'primary',
                                        style: {
                                            position: 'absolute',
                                            top: '50%',
                                            left: '50%',
                                            transform: 'translate(-50%, -50%)'
                                        }
                                    },
                                    'Choisir une image decorative'
                                )
                            }
                        )
                    ),
                    imageUrl && wp.element.createElement(
                        'img',
                        {
                            src: imageUrl,
                            alt: '',
                            style: imageStyle,
                            draggable: true,
                            onDragStart: handleDragStart,
                            onDrag: handleDrag,
                            onDragEnd: handleDragEnd,
                        }
                    ),
                    imageUrl && wp.element.createElement(
                        'div',
                        {
                            style: {
                                position: 'absolute',
                                bottom: '5px',
                                right: '5px',
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
                                    onSelect: (media) => setAttributes({ imageUrl: media.url, imageId: media.id }),
                                    allowedTypes: ['image'],
                                    render: ({ open }) => wp.element.createElement(
                                        Button,
                                        { onClick: open, variant: 'secondary', isSmall: true },
                                        'Changer'
                                    )
                                }
                            )
                        ),
                        wp.element.createElement(
                            Button,
                            {
                                onClick: () => setAttributes({ imageUrl: '', imageId: 0 }),
                                variant: 'secondary',
                                isSmall: true,
                                isDestructive: true
                            },
                            'Supprimer'
                        )
                    ),
                    imageUrl && wp.element.createElement(
                        'p',
                        {
                            style: {
                                position: 'absolute',
                                top: '5px',
                                left: '5px',
                                margin: 0,
                                padding: '2px 8px',
                                background: 'rgba(0,0,0,0.6)',
                                color: 'white',
                                borderRadius: '3px',
                                fontSize: '11px'
                            }
                        },
                        'Glissez l\'image pour la positionner'
                    )
                )
            );
        },

        save: function (props) {
            const { imageUrl, positionX, positionY, size, opacity, zIndex, hideOnMobile, flipHorizontal } = props.attributes;

            if (!imageUrl) return null;

            const className = 'decoration-image' + (hideOnMobile ? ' hide-on-mobile' : '');

            const style = {
                position: 'fixed',
                left: positionX + '%',
                top: positionY + '%',
                width: size + 'vw',
                height: 'auto',
                opacity: opacity / 100,
                zIndex: zIndex,
                pointerEvents: 'none',
                transform: flipHorizontal ? 'scaleX(-1)' : 'none',
            };

            return wp.element.createElement(
                'img',
                {
                    src: imageUrl,
                    alt: '',
                    className: className,
                    style: style,
                    'aria-hidden': 'true'
                }
            );
        }
    });
})(window.wp);
