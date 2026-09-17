import React, { useEffect, useRef } from 'react';
import * as THREE from 'three';
import { useTheme } from '@/Context/ThemeContext';

/**
 * ThreeAuthNexus: An interactive 3D mathematical Torus Knot and constellation nebula.
 * Embedded in Auth screens and major hero sections for a breathtaking futuristic visual.
 */
export default function ThreeAuthNexus({ className = '', height = null }) {
    const mountRef = useRef(null);
    const { isDark } = useTheme();
    const isDarkRef = useRef(isDark);
    isDarkRef.current = isDark;

    useEffect(() => {
        const container = mountRef.current;
        if (!container) return;

        let animationFrameId;
        let isVisible = true;

        const scene = new THREE.Scene();

        const getDimensions = () => {
            const w = container.clientWidth || (typeof window !== 'undefined' ? window.innerWidth : 800);
            const h = (typeof height === 'number' && height > 0)
                ? height
                : (container.clientHeight || (typeof window !== 'undefined' ? window.innerHeight : 600));
            return { w, h };
        };

        const { w: width, h } = getDimensions();

        const camera = new THREE.PerspectiveCamera(45, width / h, 0.1, 1000);
        camera.position.z = 5.6;

        let renderer;
        try {
            renderer = new THREE.WebGLRenderer({
                alpha: true,
                antialias: true,
                powerPreference: 'high-performance',
            });
        } catch (e) {
            return;
        }

        renderer.setSize(width, h);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.domElement.style.display = 'block';
        renderer.domElement.style.width = '100%';
        renderer.domElement.style.height = '100%';
        renderer.domElement.style.pointerEvents = 'none';
        container.appendChild(renderer.domElement);

        const group = new THREE.Group();
        scene.add(group);

        // 1. Torus Knot wireframe
        const torusGeo = new THREE.TorusKnotGeometry(1.5, 0.38, 100, 16, 2, 3);
        const torusMat = new THREE.MeshBasicMaterial({
            color: isDark ? 0x6366f1 : 0x2563eb,
            wireframe: true,
            transparent: true,
            opacity: isDark ? 0.28 : 0.2,
        });
        const torusMesh = new THREE.Mesh(torusGeo, torusMat);
        group.add(torusMesh);

        // 2. Surrounding Star Particles
        const particleCount = 180;
        const particleGeo = new THREE.BufferGeometry();
        const positions = new Float32Array(particleCount * 3);

        for (let i = 0; i < particleCount; i++) {
            positions[i * 3] = (Math.random() - 0.5) * 8;
            positions[i * 3 + 1] = (Math.random() - 0.5) * 8;
            positions[i * 3 + 2] = (Math.random() - 0.5) * 8;
        }

        particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));

        const particleMat = new THREE.PointsMaterial({
            color: isDark ? 0x38bdf8 : 0x0284c7,
            size: 0.05,
            transparent: true,
            opacity: isDark ? 0.75 : 0.65,
            blending: THREE.AdditiveBlending,
        });

        const particles = new THREE.Points(particleGeo, particleMat);
        scene.add(particles);

        let mouseX = 0;
        let mouseY = 0;
        let targetX = 0;
        let targetY = 0;

        const onMouseMove = (e) => {
            const rect = container.getBoundingClientRect();
            mouseX = ((e.clientX - rect.left) / width) * 2 - 1;
            mouseY = -(((e.clientY - rect.top) / h) * 2 - 1);
            targetY = mouseX * 0.4;
            targetX = -mouseY * 0.4;
        };

        window.addEventListener('mousemove', onMouseMove);

        const handleResize = () => {
            if (!container || !renderer || !camera) return;
            const { w: newW, h: newH } = getDimensions();
            camera.aspect = newW / newH;
            camera.updateProjectionMatrix();
            renderer.setSize(newW, newH);
        };
        window.addEventListener('resize', handleResize);

        const observer = new IntersectionObserver(([entry]) => {
            isVisible = entry.isIntersecting;
        });
        observer.observe(container);

        const handleVisibilityChange = () => {
            isVisible = !document.hidden;
        };
        document.addEventListener('visibilitychange', handleVisibilityChange);

        const animate = () => {
            animationFrameId = requestAnimationFrame(animate);
            if (!isVisible) return;

            group.rotation.x += 0.006;
            group.rotation.y += 0.008;

            group.rotation.x += (targetX - group.rotation.x) * 0.04;
            group.rotation.y += (targetY - group.rotation.y) * 0.04;

            particles.rotation.y -= 0.0015;

            const currentDark = isDarkRef.current;
            torusMat.color.setHex(currentDark ? 0x6366f1 : 0x2563eb);
            particleMat.color.setHex(currentDark ? 0x38bdf8 : 0x0284c7);

            renderer.render(scene, camera);
        };

        animate();

        return () => {
            cancelAnimationFrame(animationFrameId);
            window.removeEventListener('mousemove', onMouseMove);
            window.removeEventListener('resize', handleResize);
            document.removeEventListener('visibilitychange', handleVisibilityChange);
            if (observer) observer.disconnect();

            if (torusGeo) torusGeo.dispose();
            if (torusMat) torusMat.dispose();
            if (particleGeo) particleGeo.dispose();
            if (particleMat) particleMat.dispose();
            if (renderer) {
                if (renderer.domElement && container.contains(renderer.domElement)) {
                    container.removeChild(renderer.domElement);
                }
                renderer.dispose();
            }
        };
    }, [height]);

    return (
        <div
            ref={mountRef}
            className={`relative w-full h-full flex items-center justify-center pointer-events-none ${className}`}
            style={typeof height === 'number' && height > 0 ? { height: `${height}px` } : undefined}
        />
    );
}
