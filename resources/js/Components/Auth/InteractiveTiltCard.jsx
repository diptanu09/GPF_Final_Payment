import React, { useState, useRef, useCallback } from 'react';

/**
 * 3D Perspective Tilt Card with dynamic cursor-following spotlight glow.
 * Provides a responsive, tactile feel to the authentication form.
 */
export default function InteractiveTiltCard({ children, className = '' }) {
    const cardRef = useRef(null);
    const [tilt, setTilt] = useState({ x: 0, y: 0 });
    const [spotlight, setSpotlight] = useState({ x: 0, y: 0, opacity: 0 });

    const handleMouseMove = useCallback((e) => {
        if (!cardRef.current) return;

        const rect = cardRef.current.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;

        // Calculate normalized offset from center (-1 to 1)
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const normX = (mouseX - centerX) / centerX;
        const normY = (mouseY - centerY) / centerY;

        // Subtle tilt angle: max 4.0 degrees
        const maxAngle = 4.0;
        setTilt({
            x: -normY * maxAngle, // Rotate X (pitch)
            y: normX * maxAngle,  // Rotate Y (yaw)
        });

        setSpotlight({
            x: mouseX,
            y: mouseY,
            opacity: 1,
        });
    }, []);

    const handleMouseLeave = useCallback(() => {
        setTilt({ x: 0, y: 0 });
        setSpotlight((prev) => ({ ...prev, opacity: 0 }));
    }, []);

    return (
        <div
            style={{ perspective: 1000 }}
            className="w-full flex justify-center"
        >
            <div
                ref={cardRef}
                onMouseMove={handleMouseMove}
                onMouseLeave={handleMouseLeave}
                style={{
                    transform: `rotateX(${tilt.x}deg) rotateY(${tilt.y}deg)`,
                    transition: 'transform 0.18s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.25s ease',
                    transformStyle: 'preserve-3d',
                }}
                className={`relative rounded-2xl shadow-2xl shadow-black/70 border border-slate-800/80 backdrop-blur-xl bg-slate-900/80 overflow-hidden ${className}`}
            >
                {/* Dynamic Cursor Spotlight Effect */}
                <div
                    className="pointer-events-none absolute -inset-px transition-opacity duration-300 z-0"
                    style={{
                        opacity: spotlight.opacity,
                        background: `radial-gradient(420px circle at ${spotlight.x}px ${spotlight.y}px, rgba(99, 102, 241, 0.16), transparent 70%)`,
                    }}
                />

                {/* Border Highlight Illuminator */}
                <div
                    className="pointer-events-none absolute inset-0 rounded-2xl border border-indigo-400/20 transition-opacity duration-300 z-0"
                    style={{
                        opacity: spotlight.opacity,
                        maskImage: `radial-gradient(280px circle at ${spotlight.x}px ${spotlight.y}px, black, transparent)`,
                        WebkitMaskImage: `radial-gradient(280px circle at ${spotlight.x}px ${spotlight.y}px, black, transparent)`,
                    }}
                />

                {/* Card Content Layer */}
                <div className="relative z-10">
                    {children}
                </div>
            </div>
        </div>
    );
}
