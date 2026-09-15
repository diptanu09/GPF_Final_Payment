import React, { useEffect, useRef } from 'react';

/**
 * Interactive canvas particle constellation & ambient aurora background.
 * Adapts to mouse movements with subtle particle physics and gentle glowing nodes.
 */
export default function AuthBackground({ children, className = '' }) {
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

                    if (dist < 120) {
                        const lineAlpha = (1 - dist / 120) * 0.16;
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.strokeStyle = `rgba(129, 140, 248, ${lineAlpha})`;
                        ctx.lineWidth = 0.8;
                        ctx.stroke();
                    }
                }
            }

            // Update & render particles
            for (let i = 0; i < particles.length; i++) {
                const p = particles[i];

                // Mouse interaction physics
                if (mx !== null && my !== null) {
                    const dx = p.x - mx;
                    const dy = p.y - my;
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < mRadius && dist > 0) {
                        const force = (mRadius - dist) / mRadius;
                        const angle = Math.atan2(dy, dx);
                        p.x += Math.cos(angle) * force * 1.5;
                        p.y += Math.sin(angle) * force * 1.5;
                    }
                }

                p.x += p.vx;
                p.y += p.vy;

                // Screen boundaries wrap-around
                if (p.x < -10) p.x = width + 10;
                else if (p.x > width + 10) p.x = -10;
                if (p.y < -10) p.y = height + 10;
                else if (p.y > height + 10) p.y = -10;

                // Pulsing dot brightness
                const currentAlpha = p.alpha + Math.sin(time + p.pulseOffset) * 0.15;
                const safeAlpha = Math.max(0.1, Math.min(0.75, currentAlpha));

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
        <div className="relative min-h-screen w-full bg-slate-950 overflow-hidden font-sans select-none">
            {/* Interactive Particle Network Canvas */}
            <canvas
                ref={canvasRef}
                className="absolute inset-0 pointer-events-none z-0 opacity-70"
            />

            {/* Ambient Aurora Gradient Orbs */}
            <div className="absolute -top-28 left-1/2 -translate-x-1/2 w-[720px] h-[400px] bg-indigo-600/15 blur-[140px] rounded-full pointer-events-none animate-pulse-glow"></div>
            <div className="absolute top-1/3 -left-32 w-[520px] h-[360px] bg-cyan-600/10 blur-[130px] rounded-full pointer-events-none animate-float-gentle"></div>
            <div className="absolute bottom-12 right-[-5%] w-[580px] h-[380px] bg-emerald-600/10 blur-[140px] rounded-full pointer-events-none animate-float-gentle" style={{ animationDelay: '2.5s' }}></div>

            {/* Subtle Institutional Security Watermark (Sacred Ashoka / CAG Wheel Motif) */}
            <div className="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.035] overflow-hidden">
                <svg className="w-[850px] h-[850px] text-white animate-reverse-spin" viewBox="0 0 100 100" fill="none" stroke="currentColor">
                    <circle cx="50" cy="50" r="46" strokeWidth="0.5" strokeDasharray="2,3" />
                    <circle cx="50" cy="50" r="38" strokeWidth="0.75" />
                    <circle cx="50" cy="50" r="28" strokeWidth="0.5" strokeDasharray="4,2" />
                    <circle cx="50" cy="50" r="14" strokeWidth="0.8" />
                    {[...Array(24)].map((_, i) => {
                        const angle = (i * 360) / 24;
                        const rad = (angle * Math.PI) / 180;
                        const x2 = 50 + Math.cos(rad) * 28;
                        const y2 = 50 + Math.sin(rad) * 28;
                        return (
                            <line
                                key={i}
                                x1="50"
                                y1="50"
                                x2={x2}
                                y2={y2}
                                strokeWidth="0.3"
                            />
                        );
                    })}
                </svg>
            </div>

            {/* Centered Content Container */}
            <div className={`relative z-10 w-full min-h-screen flex flex-col justify-center items-center px-4 py-8 sm:py-12 ${className}`}>
                {children}
            </div>
        </div>
    );
}
