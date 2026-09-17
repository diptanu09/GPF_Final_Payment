/**
 * AudioFeedbackService: High-end, subtle acoustic micro-feedback for UI interactions.
 * Synthesizes organic micro-clicks and chimes using the browser's native Web Audio API.
 * Supports complete muting persisted in localStorage.
 */
class AudioFeedbackService {
    constructor() {
        this.ctx = null;
        this.isMuted = false;
        if (typeof window !== 'undefined') {
            this.isMuted = localStorage.getItem('gpffp_muted') === 'true';
        }
    }

    initContext() {
        if (!this.ctx && typeof window !== 'undefined') {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                this.ctx = new AudioContext();
            }
        }
        if (this.ctx && this.ctx.state === 'suspended') {
            this.ctx.resume().catch(() => {});
        }
    }

    toggleMute() {
        this.isMuted = !this.isMuted;
        try {
            localStorage.setItem('gpffp_muted', this.isMuted ? 'true' : 'false');
        } catch (e) {}
        if (!this.isMuted) {
            this.playToggle();
        }
        return this.isMuted;
    }

    getMuted() {
        return this.isMuted;
    }

    playClick() {
        if (this.isMuted) return;
        this.initContext();
        if (!this.ctx) return;

        try {
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(800, this.ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(400, this.ctx.currentTime + 0.03);

            gain.gain.setValueAtTime(0.04, this.ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, this.ctx.currentTime + 0.03);

            osc.connect(gain);
            gain.connect(this.ctx.destination);

            osc.start();
            osc.stop(this.ctx.currentTime + 0.03);
        } catch (e) {}
    }

    playToggle() {
        if (this.isMuted) return;
        this.initContext();
        if (!this.ctx) return;

        try {
            const now = this.ctx.currentTime;
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();

            osc.type = 'triangle';
            osc.frequency.setValueAtTime(520, now);
            osc.frequency.exponentialRampToValueAtTime(780, now + 0.06);

            gain.gain.setValueAtTime(0.05, now);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.07);

            osc.connect(gain);
            gain.connect(this.ctx.destination);

            osc.start();
            osc.stop(now + 0.07);
        } catch (e) {}
    }

    playSuccess() {
        if (this.isMuted) return;
        this.initContext();
        if (!this.ctx) return;

        try {
            const now = this.ctx.currentTime;
            [523.25, 659.25, 783.99].forEach((freq, i) => {
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, now + i * 0.05);

                gain.gain.setValueAtTime(0.04, now + i * 0.05);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + i * 0.05 + 0.18);

                osc.connect(gain);
                gain.connect(this.ctx.destination);

                osc.start(now + i * 0.05);
                osc.stop(now + i * 0.05 + 0.18);
            });
        } catch (e) {}
    }

    playCommand() {
        if (this.isMuted) return;
        this.initContext();
        if (!this.ctx) return;

        try {
            const now = this.ctx.currentTime;
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(440, now);
            osc.frequency.exponentialRampToValueAtTime(880, now + 0.05);

            gain.gain.setValueAtTime(0.035, now);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.05);

            osc.connect(gain);
            gain.connect(this.ctx.destination);

            osc.start();
            osc.stop(now + 0.05);
        } catch (e) {}
    }
}

export const audioFeedback = new AudioFeedbackService();
export default audioFeedback;
