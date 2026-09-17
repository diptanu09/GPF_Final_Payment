import React, { useEffect, useRef } from 'react';
import * as THREE from 'three';
import { useTheme } from '@/Context/ThemeContext';

/**
 * ThreeDashboardGlobe: A hardware-accelerated 3D settlement nexus.
 * Renders an interconnected geometric sphere with dynamic particle cloud.
 * Features mouse raycast tracking, theme responsiveness, and visibility suspension.
 */
export default function ThreeDashboardGlobe({ className = '', height = 260 }) {
    const mountRef = useRef(null);
    const { isDark } = useTheme();
    const isDarkRef = useRef(isDark);
    isDarkRef.current = isDark;

    useEffect(() => {
        const container = mountRef.current;
        if (!container) return;

        let animationFrameId;
        let isVisible = true;

        // Scene setup
        const scene = new THREE.Scene();
        const width = container.clientWidth || 300;
        const h = height;

        const camera = new THREE.PerspectiveCamera(45, width / h, 0.1, 1000);
        camera.position.z = 5.5;

        // Renderer with antialiasing and alpha
        let renderer;
        try {
            renderer = new THREE.WebGLRenderer({
                alpha: true,
                antialias: true,
                powerPreference: 'high-performance',
            });
        } catch (e) {
            // WebGL not supported fallback
            return;
        }

        renderer.setSize(width, h);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        container.appendChild(renderer.domElement);

        // Core Group
        const group = new THREE.Group();
        scene.add(group);

        // 1. Outer Icosahedron Wireframe
        const icoGeo = new THREE.IcosahedronGeometry(2.1, 2);
        const wireMat = new THREE.MeshBasicMaterial({
            color: isDark ? 0x6366f1 : 0x3b82f6,
            wireframe: true,
            transparent: true,
            opacity: isDark ? 0.22 : 0.18,
        });
        const icoMesh = new THREE.Mesh(icoGeo, wireMat);
        group.add(icoMesh);

        // 2. Inner Pulsing Core
        const coreGeo = new THREE.IcosahedronGeometry(1.2, 1);
        const coreMat = new THREE.MeshBasicMaterial({
            color: isDark ? 0x38bdf8 : 0x0284c7,
            wireframe: true,
            transparent: true,
            opacity: isDark ? 0.35 : 0.25,
        });
        const coreMesh = new THREE.Mesh(coreGeo, coreMat);
        group.add(coreMesh);

        // 3. Orbiting Particles
        const particleCount = 120;
        const particleGeo = new THREE.BufferGeometry();
        const positions = new Float32Array(particleCount * 3);
        const originalPositions = [];

        for (let i = 0; i < particleCount; i++) {
            const radius = 2.1 + (Math.random() - 0.5) * 0.4;
            const theta = Math.random() * Math.PI * 2;
            const phi = Math.acos(Math.random() * 2 - 1);

            const x = radius * Math.sin(phi) * Math.cos(theta);
            const y = radius * Math.sin(phi) * Math.sin(theta);
            const z = radius * Math.cos(phi);

            positions[i * 3] = x;
            positions[i * 3 + 1] = y;
            positions[i * 3 + 2] = z;
            originalPositions.push({ x, y, z, theta, phi, radius });
        }

        particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));

        const particleMat = new THREE.PointsMaterial({
            color: isDark ? 0x34d399 : 0x2563eb,
            size: 0.055,
            transparent: true,
            opacity: isDark ? 0.85 : 0.75,
            blending: THREE.AdditiveBlending,
        });

        const particles = new THREE.Points(particleGeo, particleMat);
        group.add(particles);

        // Mouse coordinates for gentle camera parallax
        let targetRotationX = 0;
        let targetRotationY = 0;
        let mouseX = 0;
        let mouseY = 0;

        const onMouseMove = (event) => {
            const rect = container.getBoundingClientRect();
            mouseX = ((event.clientX - rect.left) / width) * 2 - 1;
            mouseY = -(((event.clientY - rect.top) / h) * 2 - 1);
            targetRotationY = mouseX * 0.5;
            targetRotationX = -mouseY * 0.5;
        };

        container.addEventListener('mousemove', onMouseMove);

        // Responsive Resize
        const handleResize = () => {
            if (!container) return;
            const newW = container.clientWidth;
            camera.aspect = newW / h;
            camera.updateProjectionMatrix();
            renderer.setSize(newW, h);
        };
        window.addEventListener('resize', handleResize);

        // Visibility Observer (Suspend rendering when scrolled away)
        const observer = new IntersectionObserver(
            ([entry]) => {
                isVisible = entry.isIntersecting;
            },
            { threshold: 0.05 }
        );
        observer.observe(container);

        // Document visibility change (Suspend when browser tab inactive)
        const handleVisibilityChange = () => {
            isVisible = !document.hidden;
        };
        document.addEventListener('visibilitychange', handleVisibilityChange);

        // Animation Loop
        let clock = new THREE.Clock();

        const animate = () => {
            animationFrameId = requestAnimationFrame(animate);
            if (!isVisible) return;

            const elapsedTime = clock.getElapsedTime();

            // Smooth rotation with mouse influence
            group.rotation.y += 0.005;
            group.rotation.x += (targetRotationX - group.rotation.x) * 0.05;
            group.rotation.y += (targetRotationY - (group.rotation.y % (Math.PI * 2))) * 0.02;

            // Inner core counter-rotation
            coreMesh.rotation.x -= 0.008;
            coreMesh.rotation.z += 0.006;

            // Gentle pulsing
            const pulse = Math.sin(elapsedTime * 1.5) * 0.03 + 1;
            coreMesh.scale.set(pulse, pulse, pulse);

            // Dynamically update materials if theme switched
            const currentDark = isDarkRef.current;
            wireMat.color.setHex(currentDark ? 0x6366f1 : 0x3b82f6);
            wireMat.opacity = currentDark ? 0.22 : 0.18;
            coreMat.color.setHex(currentDark ? 0x38bdf8 : 0x0284c7);
            particleMat.color.setHex(currentDark ? 0x34d399 : 0x2563eb);

            renderer.render(scene, camera);
        };

        animate();

        // Cleanup
        return () => {
            cancelAnimationFrame(animationFrameId);
            container.removeEventListener('mousemove', onMouseMove);
            window.removeEventListener('resize', handleResize);
            document.removeEventListener('visibilitychange', handleVisibilityChange);
            observer.disconnect();

            icoGeo.dispose();
            wireMat.dispose();
            coreGeo.dispose();
            coreMat.dispose();
            particleGeo.dispose();
            particleMat.dispose();
            if (renderer.domElement && container.contains(renderer.domElement)) {
                container.removeChild(renderer.domElement);
            }
            renderer.dispose();
        };
    }, [height]);

    return (
        <div
            ref={mountRef}
            className={`relative flex items-center justify-center overflow-hidden pointer-events-auto ${className}`}
            style={{ height: `${height}px` }}
        />
    );
}
