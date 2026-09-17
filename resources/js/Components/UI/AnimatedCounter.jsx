import React, { useState, useEffect, useRef } from 'react';

/**
 * AnimatedCounter: Smooth rolling count-up animation for numeric stats & currency values.
 */
export default function AnimatedCounter({
    value = 0,
    duration = 1000,
    prefix = '',
    suffix = '',
    formatNumber = true,
    className = '',
}) {
    const [displayValue, setDisplayValue] = useState(0);
    const startValueRef = useRef(0);
    const startTimeRef = useRef(null);

    const targetValue = typeof value === 'number' ? value : parseFloat(value) || 0;

    useEffect(() => {
        let animationFrameId;
        startValueRef.current = displayValue;
        startTimeRef.current = null;

        const animate = (timestamp) => {
            if (!startTimeRef.current) startTimeRef.current = timestamp;
            const progress = Math.min((timestamp - startTimeRef.current) / duration, 1);

            // Ease-out cubic formula
            const easeOutProgress = 1 - Math.pow(1 - progress, 3);
            const current = startValueRef.current + (targetValue - startValueRef.current) * easeOutProgress;

            setDisplayValue(current);

            if (progress < 1) {
                animationFrameId = requestAnimationFrame(animate);
            } else {
                setDisplayValue(targetValue);
            }
        };

        animationFrameId = requestAnimationFrame(animate);

        return () => cancelAnimationFrame(animationFrameId);
    }, [targetValue, duration]);

    const formatted = formatNumber
        ? Math.round(displayValue).toLocaleString('en-IN')
        : Math.round(displayValue);

    return (
        <span className={className}>
            {prefix}
            {formatted}
            {suffix}
        </span>
    );
}
