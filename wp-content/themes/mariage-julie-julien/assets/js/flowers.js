/**
 * Fleurs animées - Mariage Julie & Julien
 * Inspiré du code canvas de pétales avec bloom automatique
 */
(function () {
    'use strict';

    function Transition(startValue, endValue, type, duration, delay) {
        this.start = startValue;
        this.end = endValue;
        this.duration = duration || 1;
        this.delay = delay || 0;
        this.type = type || 'easeInOut';
        this.done = false;
        this.startTime = Date.now();

        this.setValue = function (start, end, t, dur, del) {
            this.start = start;
            this.end = end;
            this.duration = dur || this.duration;
            this.delay = del || 0;
            this.type = t || this.type;
            this.done = false;
            this.startTime = Date.now();
        };

        this.giveValue = function () {
            var delta = this.end - this.start;
            var elapsed = ((Date.now() - this.startTime) / 1000 - this.delay) / this.duration;
            var tf;
            switch (this.type) {
                case 'easeOut':
                    tf = 1 - Math.pow(1 - elapsed, 4);
                    break;
                case 'easeInOut':
                    tf = elapsed < 0.5
                        ? Math.pow(elapsed * 2, 4) / 2
                        : (2 - Math.pow((1 - elapsed) * 2, 4)) / 2;
                    break;
                case 'easeOutBack':
                    var j = 0.45;
                    tf = 1 - (-2 * Math.pow(1 - elapsed, 3) + 3 * j * Math.pow(1 - elapsed, 2)) / (3 * j - 2);
                    break;
                default:
                    tf = elapsed;
            }
            if (elapsed >= 1) { this.done = true; return this.end; }
            if (elapsed <= 0) { return this.start; }
            return this.start + delta * tf;
        };
    }

    // Palette mariage
    var palette = {
        sage:       { h: 105, s: 18, l: 61 },
        sageDark:   { h: 105, s: 25, l: 48 },
        champagne:  { h: 35,  s: 55, l: 88 },
        ivory:      { h: 60,  s: 100, l: 97 },
        rose:       { h: 5,   s: 30, l: 78 },
        dustyPink:  { h: 350, s: 25, l: 72 },
        cream:      { h: 40,  s: 40, l: 90 },
        warmGreen:  { h: 95,  s: 20, l: 55 },
    };

    var flowerPresets = [
        { colors: [palette.sage, palette.warmGreen, palette.cream], type: 'default', num: 8, layers: 5 },
        { colors: [palette.dustyPink, palette.rose, palette.cream], type: 'default', num: 7, layers: 5 },
        { colors: [palette.champagne, palette.cream, palette.ivory], type: 'gold', num: 10, layers: 5 },
        { colors: [palette.sage, palette.sageDark, palette.champagne], type: 'default', num: 9, layers: 6 },
        { colors: [palette.rose, palette.champagne, palette.cream], type: 'default', num: 6, layers: 5 },
    ];

    function Petal(x, y, ang, l, color, max, dur, del, type) {
        var transition = 'easeInOut';
        this.scale = new Transition(-0.05, max, transition, dur, del);
        this.x = x;
        this.y = y;
        this.color = color;

        if (type === 'gold') {
            var shade = [];
            for (var i = 0; i < 15; i++) shade.push(Math.random());
        }

        this.upd = function (ctx) {
            var size = this.scale.giveValue();
            ctx.save();
            ctx.translate(this.x, this.y);
            ctx.rotate(ang);
            ctx.scale(size <= 1 ? size : -size + 2, 1);

            if (type === 'gold') {
                var idx = Math.max(0, Math.floor(size * 14));
                var lightness = size >= 0 ? 60 + 30 * shade[idx] : 80;
                ctx.fillStyle = 'hsla(' + this.color.h + ',' + (size >= 0 ? this.color.s : 0) + '%,' + lightness + '%,0.7)';
                ctx.strokeStyle = 'hsla(' + this.color.h + ',' + this.color.s + '%,' + (lightness - 15) + '%,0.3)';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(0, 0);
                ctx.bezierCurveTo(l * 0.3, -l * 0.35, l * 0.7, -l * 0.3, l * 0.85, 0);
                ctx.bezierCurveTo(l * 0.7, l * 0.3, l * 0.3, l * 0.35, 0, 0);
                ctx.closePath();
                ctx.fill();
                ctx.stroke();
            } else {
                var sl = size >= 0 ? this.color.l + size * 20 : 95;
                ctx.fillStyle = 'hsla(' + this.color.h + ',' + (size >= 0 ? this.color.s : 0) + '%,' + sl + '%,0.65)';
                ctx.strokeStyle = 'hsla(' + this.color.h + ',' + this.color.s + '%,' + (sl - 10) + '%,0.2)';
                ctx.lineWidth = 0.8;
                ctx.beginPath();
                ctx.moveTo(0, 0);
                ctx.bezierCurveTo(l * 0.25, -l * 0.4, l * 0.75, -l * 0.4, l, 0);
                ctx.bezierCurveTo(l * 0.75, l * 0.4, l * 0.25, l * 0.4, 0, 0);
                ctx.closePath();
                ctx.fill();
                ctx.stroke();
            }
            ctx.restore();
        };
    }

    function Flower(x, y, size, preset, bloomDelay) {
        var p = flowerPresets[preset % flowerPresets.length];
        this.x = x;
        this.y = y;
        this.rad = size;
        this.petals = [];
        this.bloomed = false;

        var num = p.num;
        var layers = p.layers;
        var innerRad = this.rad / 3;
        var random = 0.15;
        var delayMult = 0.1;
        var petalDur = 1.2;

        for (var j = 0; j < layers; j++) {
            var colorIdx = Math.min(j, p.colors.length - 1);
            var color = p.colors[Math.floor(j * (p.colors.length - 1) / (layers - 1))];
            for (var i = 0; i < num; i++) {
                var angle = i * 2 * Math.PI / num + j * 0.35;
                var setRad = this.rad + (innerRad - this.rad) * j / (layers - 1);
                var del = Math.random() * random + j * delayMult + (bloomDelay || 0);
                var maxScale = 0.3 + 0.7 * (layers - 1 - j) / (layers - 1);
                this.petals.push(new Petal(
                    x + Math.cos(angle) * setRad,
                    y + Math.sin(angle) * setRad,
                    angle, setRad * 3.5, color, maxScale, petalDur, del, p.type
                ));
            }
        }

        // Centre de la fleur
        this.drawCenter = function (ctx) {
            var firstPetal = this.petals[this.petals.length - 1];
            var s = firstPetal ? firstPetal.scale.giveValue() : 0;
            if (s <= 0) return;
            var centerColor = p.colors[p.colors.length - 1];
            ctx.fillStyle = 'hsla(' + centerColor.h + ',' + centerColor.s + '%,' + (centerColor.l - 10) + '%,' + Math.min(s, 0.6) + ')';
            ctx.beginPath();
            ctx.arc(x, y, innerRad * 0.8 * Math.min(s * 2, 1), 0, 2 * Math.PI);
            ctx.fill();
        };

        this.upd = function (ctx) {
            // Base sombre
            var firstScale = this.petals[0].scale.giveValue();
            if (firstScale > 0) {
                ctx.fillStyle = 'hsla(' + p.colors[0].h + ',' + p.colors[0].s + '%,25%,' + Math.min(firstScale, 0.3) + ')';
                ctx.beginPath();
                ctx.arc(x, y, this.rad * 0.9, 0, 2 * Math.PI);
                ctx.fill();
            }
            for (var i = 0; i < this.petals.length; i++) {
                this.petals[i].upd(ctx);
            }
            this.drawCenter(ctx);
        };
    }

    // Feuille decorative
    function Leaf(x, y, angle, length, delay) {
        this.scale = new Transition(0, 1, 'easeOut', 1.5, delay);

        this.draw = function (ctx) {
            var s = this.scale.giveValue();
            if (s <= 0.01) return;
            ctx.save();
            ctx.translate(x, y);
            ctx.rotate(angle);
            ctx.scale(s, s);
            ctx.fillStyle = 'hsla(105, 20%, 58%, 0.25)';
            ctx.strokeStyle = 'hsla(105, 20%, 48%, 0.3)';
            ctx.lineWidth = 0.8;
            ctx.beginPath();
            ctx.moveTo(0, 0);
            ctx.bezierCurveTo(length * 0.2, -length * 0.3, length * 0.7, -length * 0.25, length, 0);
            ctx.bezierCurveTo(length * 0.7, length * 0.25, length * 0.2, length * 0.3, 0, 0);
            ctx.fill();
            ctx.stroke();
            // Nervure
            ctx.strokeStyle = 'hsla(105, 20%, 48%, 0.15)';
            ctx.beginPath();
            ctx.moveTo(2, 0);
            ctx.lineTo(length - 3, 0);
            ctx.stroke();
            ctx.restore();
        };
    }

    // Branche avec feuilles
    function Branch(startX, startY, endX, endY, delay, leafCount) {
        this.leaves = [];
        var dx = endX - startX;
        var dy = endY - startY;
        var len = Math.sqrt(dx * dx + dy * dy);
        var branchAngle = Math.atan2(dy, dx);

        for (var i = 0; i < (leafCount || 5); i++) {
            var t = 0.15 + 0.7 * i / (leafCount - 1);
            var lx = startX + dx * t;
            var ly = startY + dy * t;
            var side = i % 2 === 0 ? -1 : 1;
            var leafAngle = branchAngle + side * (0.4 + Math.random() * 0.4);
            var leafLen = 15 + Math.random() * 20;
            this.leaves.push(new Leaf(lx, ly, leafAngle, leafLen, delay + i * 0.12));
        }

        this.stemScale = new Transition(0, 1, 'easeOut', 1.2, delay);

        this.draw = function (ctx) {
            var s = this.stemScale.giveValue();
            if (s <= 0.01) return;
            ctx.strokeStyle = 'hsla(105, 18%, 55%, 0.3)';
            ctx.lineWidth = 1.2;
            ctx.beginPath();
            ctx.moveTo(startX, startY);
            ctx.lineTo(startX + dx * s, startY + dy * s);
            ctx.stroke();
            for (var i = 0; i < this.leaves.length; i++) {
                this.leaves[i].draw(ctx);
            }
        };
    }

    // ========== SCENE SETUP ==========
    function FlowerScene(canvasId) {
        var can = document.getElementById(canvasId);
        if (!can) return null;
        var ctx = can.getContext('2d');
        var parent = can.parentElement;
        this.flowers = [];
        this.branches = [];
        this.started = false;
        var self = this;

        function resize() {
            can.width = parent.offsetWidth;
            can.height = parent.offsetHeight;
        }
        resize();
        window.addEventListener('resize', resize);

        this.addFlower = function (xRatio, yRatio, size, preset, delay) {
            self.flowers.push(new Flower(
                can.width * xRatio, can.height * yRatio,
                size, preset, delay
            ));
        };

        this.addBranch = function (x1r, y1r, x2r, y2r, delay, leafCount) {
            self.branches.push(new Branch(
                can.width * x1r, can.height * y1r,
                can.width * x2r, can.height * y2r,
                delay, leafCount
            ));
        };

        this.start = function () {
            if (self.started) return;
            self.started = true;
            resize();
            // Re-create elements at correct positions after resize
            var fSpecs = self._flowerSpecs || [];
            var bSpecs = self._branchSpecs || [];
            self.flowers = [];
            self.branches = [];
            fSpecs.forEach(function (f) {
                self.flowers.push(new Flower(can.width * f[0], can.height * f[1], f[2], f[3], f[4]));
            });
            bSpecs.forEach(function (b) {
                self.branches.push(new Branch(can.width * b[0], can.height * b[1], can.width * b[2], can.height * b[3], b[4], b[5]));
            });
            anim();
        };

        this.addFlowerSpec = function (xr, yr, size, preset, delay) {
            if (!self._flowerSpecs) self._flowerSpecs = [];
            self._flowerSpecs.push([xr, yr, size, preset, delay]);
        };

        this.addBranchSpec = function (x1r, y1r, x2r, y2r, delay, leafCount) {
            if (!self._branchSpecs) self._branchSpecs = [];
            self._branchSpecs.push([x1r, y1r, x2r, y2r, delay, leafCount]);
        };

        function anim() {
            ctx.clearRect(0, 0, can.width, can.height);
            for (var i = 0; i < self.branches.length; i++) {
                self.branches[i].draw(ctx);
            }
            for (var i = 0; i < self.flowers.length; i++) {
                self.flowers[i].upd(ctx);
            }
            requestAnimationFrame(anim);
        }
    }

    // ========== INIT ==========
    function initScenes() {
        // Hero scene - BEAUCOUP de fleurs
        var hero = new FlowerScene('hero-canvas');
        if (hero) {
            // Grosses fleurs coins
            hero.addFlowerSpec(0.06, 0.08, 40, 0, 0.2);
            hero.addFlowerSpec(0.94, 0.08, 38, 1, 0.3);
            hero.addFlowerSpec(0.04, 0.92, 42, 3, 0.5);
            hero.addFlowerSpec(0.96, 0.92, 40, 4, 0.4);
            // Grosses fleurs bords
            hero.addFlowerSpec(0.03, 0.45, 35, 2, 0.6);
            hero.addFlowerSpec(0.97, 0.5, 33, 0, 0.7);
            hero.addFlowerSpec(0.5, 0.03, 28, 1, 0.8);
            hero.addFlowerSpec(0.45, 0.97, 30, 3, 0.9);
            // Moyennes coins
            hero.addFlowerSpec(0.14, 0.06, 25, 4, 0.8);
            hero.addFlowerSpec(0.86, 0.06, 24, 2, 0.9);
            hero.addFlowerSpec(0.12, 0.94, 26, 1, 1.0);
            hero.addFlowerSpec(0.88, 0.94, 24, 0, 1.1);
            // Moyennes bords
            hero.addFlowerSpec(0.08, 0.3, 22, 3, 1.0);
            hero.addFlowerSpec(0.92, 0.35, 20, 4, 1.1);
            hero.addFlowerSpec(0.1, 0.65, 22, 2, 1.2);
            hero.addFlowerSpec(0.9, 0.7, 20, 1, 1.3);
            hero.addFlowerSpec(0.25, 0.04, 18, 0, 1.2);
            hero.addFlowerSpec(0.75, 0.04, 16, 3, 1.3);
            hero.addFlowerSpec(0.3, 0.96, 20, 2, 1.1);
            hero.addFlowerSpec(0.7, 0.96, 18, 4, 1.2);
            // Petites dispersees
            hero.addFlowerSpec(0.2, 0.15, 14, 1, 1.4);
            hero.addFlowerSpec(0.8, 0.18, 12, 3, 1.5);
            hero.addFlowerSpec(0.15, 0.82, 15, 0, 1.3);
            hero.addFlowerSpec(0.85, 0.85, 13, 2, 1.6);
            hero.addFlowerSpec(0.06, 0.6, 12, 4, 1.5);
            hero.addFlowerSpec(0.94, 0.62, 14, 1, 1.4);
            hero.addFlowerSpec(0.22, 0.95, 16, 3, 1.3);
            hero.addFlowerSpec(0.78, 0.95, 14, 0, 1.5);
            hero.addFlowerSpec(0.35, 0.05, 10, 2, 1.6);
            hero.addFlowerSpec(0.65, 0.05, 11, 4, 1.7);
            // Tres petites
            hero.addFlowerSpec(0.18, 0.4, 8, 0, 1.8);
            hero.addFlowerSpec(0.82, 0.42, 9, 2, 1.9);
            hero.addFlowerSpec(0.25, 0.85, 10, 1, 1.7);
            hero.addFlowerSpec(0.75, 0.88, 8, 3, 1.8);

            // Branches - beaucoup plus
            hero.addBranchSpec(0, 0.05, 0.2, 0.2, 0.1, 7);
            hero.addBranchSpec(1, 0.05, 0.8, 0.18, 0.2, 7);
            hero.addBranchSpec(0, 0.95, 0.22, 0.78, 0.3, 6);
            hero.addBranchSpec(1, 0.95, 0.78, 0.8, 0.4, 6);
            hero.addBranchSpec(0.02, 0.3, 0.15, 0.5, 0.5, 5);
            hero.addBranchSpec(0.98, 0.3, 0.85, 0.48, 0.6, 5);
            hero.addBranchSpec(0.02, 0.6, 0.14, 0.78, 0.7, 5);
            hero.addBranchSpec(0.98, 0.6, 0.86, 0.8, 0.8, 5);
            hero.addBranchSpec(0.15, 0.02, 0.35, 0.08, 0.9, 4);
            hero.addBranchSpec(0.85, 0.02, 0.65, 0.07, 1.0, 4);
            hero.addBranchSpec(0.1, 0.98, 0.35, 0.92, 0.8, 5);
            hero.addBranchSpec(0.9, 0.98, 0.65, 0.93, 0.9, 5);

            hero.start();
        }

        // Sections: beaucoup plus de fleurs + fleurs en bas
        var sectionCanvases = document.querySelectorAll('.section-canvas');
        sectionCanvases.forEach(function (can) {
            var scene = new FlowerScene(can.id);
            if (!scene) return;

            var rp = function() { return Math.floor(Math.random() * 5); };

            // Fleurs autour du titre
            scene.addFlowerSpec(0.03, 0.15, 22, rp(), 0.2);
            scene.addFlowerSpec(0.97, 0.15, 22, rp(), 0.3);
            scene.addFlowerSpec(0.08, 0.08, 16, rp(), 0.4);
            scene.addFlowerSpec(0.92, 0.08, 16, rp(), 0.5);
            scene.addFlowerSpec(0.15, 0.2, 12, rp(), 0.6);
            scene.addFlowerSpec(0.85, 0.2, 12, rp(), 0.7);

            // Fleurs qui "poussent" du bas - grosses
            scene.addFlowerSpec(0.08, 0.92, 30, rp(), 0.3);
            scene.addFlowerSpec(0.2, 0.95, 25, rp(), 0.5);
            scene.addFlowerSpec(0.35, 0.93, 20, rp(), 0.7);
            scene.addFlowerSpec(0.5, 0.96, 22, rp(), 0.6);
            scene.addFlowerSpec(0.65, 0.94, 18, rp(), 0.8);
            scene.addFlowerSpec(0.8, 0.95, 25, rp(), 0.4);
            scene.addFlowerSpec(0.92, 0.92, 30, rp(), 0.3);
            // Fleurs bas moyennes
            scene.addFlowerSpec(0.12, 0.88, 15, rp(), 0.9);
            scene.addFlowerSpec(0.28, 0.9, 14, rp(), 1.0);
            scene.addFlowerSpec(0.42, 0.91, 12, rp(), 1.1);
            scene.addFlowerSpec(0.58, 0.9, 14, rp(), 1.0);
            scene.addFlowerSpec(0.72, 0.89, 16, rp(), 0.9);
            scene.addFlowerSpec(0.88, 0.88, 15, rp(), 1.0);

            // Branches montantes du bas
            scene.addBranchSpec(0.05, 1, 0.1, 0.8, 0.1, 6);
            scene.addBranchSpec(0.15, 1, 0.22, 0.82, 0.2, 5);
            scene.addBranchSpec(0.3, 1, 0.35, 0.85, 0.3, 4);
            scene.addBranchSpec(0.5, 1, 0.48, 0.87, 0.4, 4);
            scene.addBranchSpec(0.7, 1, 0.65, 0.84, 0.3, 5);
            scene.addBranchSpec(0.85, 1, 0.78, 0.82, 0.2, 5);
            scene.addBranchSpec(0.95, 1, 0.9, 0.8, 0.1, 6);
            // Branches laterales du haut
            scene.addBranchSpec(0, 0.1, 0.12, 0.25, 0.4, 4);
            scene.addBranchSpec(1, 0.1, 0.88, 0.22, 0.5, 4);

            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        scene.start();
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.2 });

            observer.observe(can);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScenes);
    } else {
        initScenes();
    }
})();
