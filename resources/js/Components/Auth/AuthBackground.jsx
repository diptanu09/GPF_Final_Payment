import React, { useEffect, useRef } from 'react';
import ThreeAuthNexus from '@/Components/Visuals/ThreeAuthNexus';
import { ThemeProvider } from '@/Context/ThemeContext';
import ThemeToggle from '@/Components/UI/ThemeToggle';

/**
 * Interactive canvas particle constellation & ambient 3D Torus Knot background.
 * Adapts to mouse movements with particle physics, WebGL 3D geometry, and theme switching.
 */
function AuthBackgroundContent({ children, className = '' }) {
    const canvasRef = useRef(null);
    const mouseRef = useRef({ x: null, y: null, radius: 140 });

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        let animationFrameId;
        let width = (canvas.width = window.innerWidth);
        let height = (canvas.height = window.innerHeight);

        const handleResize = () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        };

        window.addEventListener('resize', handleResize);

        // Track cursor for particle repulsion/attraction
        const handleMouseMove = (e) => {
            mouseRef.current.x = e.clientX;
            mouseRef.current.y = e.clientY;
        };

        const handleMouseLeave = () => {
            mouseRef.current.x = null;
            mouseRef.current.y = null;
        };

        window.addEventListener('mousemove', handleMouseMove);
        window.addEventListener('mouseleave', handleMouseLeave);

        // Generate particles
        const particleCount = Math.min(Math.floor((width * height) / 25000), 55);
        const particles = [];

        const colors = [
            'rgba(99, 102, 241, ', // Indigo
            'rgba(129, 140, 248, ', // Light Indigo
            'rgba(56, 189, 248, ',  // Sky Blue / Cyan
            'rgba(52, 211, 153, ',  // Emerald
        ];

        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.45,
                vy: (Math.random() - 0.5) * 0.45,
                radius: Math.random() * 2 + 1.2,
                colorBase: colors[Math.floor(Math.random() * colors.length)],
                alpha: Math.random() * 0.45 + 0.25,
                pulseSpeed: Math.random() * 0.02 + 0.008,
                pulseOffset: Math.random() * Math.PI * 2,
            });
        }

        let time = 0;

        const render = () => {
            time += 0.02;
            ctx.clearRect(0, 0, width, height);

            const mx = mouseRef.current.x;
            const my = mouseRef.current.y;
            const mRadius = mouseRef.current.radius;

            // Draw connecting lines first
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < 115) {
                        const lineAlpha = (1 - dist / 115) * 0.18;
                        ctx.beginPath();
                        ctx.strokeStyle = `rgba(129, 140, 248, ${lineAlpha})`;
                        ctx.lineWidth = 0.85;
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.stroke();
                    }
                }
            }

            // Update & Draw particles
            for (let i = 0; i < particles.length; i++) {
                const p = particles[i];

                p.x += p.vx;
                p.y += p.vy;

                // Bounce at edges
                if (p.x < 0 || p.x > width) p.vx *= -1;
                if (p.y < 0 || p.y > height) p.vy *= -1;

                // Mouse interaction
                if (mx !== null && my !== null) {
                    const dx = mx - p.x;
                    const dy = my - p.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < mRadius) {
                        const force = (mRadius - dist) / mRadius;
                        p.x -= (dx / dist) * force * 1.8;
                        p.y -= (dy / dist) * force * 1.8;
                    }
                }

                const currentAlpha = p.alpha + Math.sin(time * p.pulseSpeed * 60 + p.pulseOffset) * 0.15;
                const safeAlpha = Math.max(0.1, Math.min(0.85, currentAlpha));

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fillStyle = `${p.colorBase}${safeAlpha})`;
                ctx.shadowColor = `${p.colorBase}0.8)`;
                ctx.shadowBlur = 8;
                ctx.fill();
                ctx.shadowBlur = 0;
            }

            animationFrameId = requestAnimationFrame(render);
        };

        render();

        return () => {
            window.removeEventListener('resize', handleResize);
            window.removeEventListener('mousemove', handleMouseMove);
            window.removeEventListener('mouseleave', handleMouseLeave);
            cancelAnimationFrame(animationFrameId);
        };
    }, []);

    return (
        <div className="relative min-h-screen w-full bg-slate-950 dark:bg-slate-950 overflow-hidden font-sans select-none">
            {/* Ambient 3D Three.js Torus Knot Nexus */}
            <div className="absolute inset-0 flex items-center justify-center pointer-events-none opacity-30 z-0">
                <ThreeAuthNexus height={600} className="w-full h-full max-w-2xl" />
            </div>

            {/* Interactive Particle Network Canvas */}
            <canvas
                ref={canvasRef}
                className="absolute inset-0 pointer-events-none z-0 opacity-60"
            />

            {/* Ambient Aurora Gradient Orbs */}
            <div className="absolute -top-28 left-1/2 -translate-x-1/2 w-[720px] h-[400px] bg-indigo-600/15 blur-[140px] rounded-full pointer-events-none animate-pulse-glow"></div>
            <div className="absolute top-1/3 -left-32 w-[520px] h-[360px] bg-cyan-600/10 blur-[130px] rounded-full pointer-events-none animate-float-gentle"></div>
            <div className="absolute bottom-12 right-[-5%] w-[580px] h-[380px] bg-emerald-600/10 blur-[140px] rounded-full pointer-events-none animate-float-gentle" style={{ animationDelay: '2.5s' }}></div>

            {/* Top Bar Theme Switcher */}
            <div className="absolute top-4 right-4 z-20">
                <ThemeToggle />
            </div>

            {/* Centered Content Container */}
            <div className={`relative z-10 w-full min-h-screen flex flex-col justify-center items-center px-4 py-8 sm:py-12 ${className}`}>
                {children}
            </div>
        </div>
    );
}

export default function AuthBackground(props) {
    return (
        <ThemeProvider>
            <AuthBackgroundContent {...props} />
        </ThemeProvider>
    );
}
