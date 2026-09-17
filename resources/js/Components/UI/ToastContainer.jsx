import React, { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { CheckCircle2, AlertCircle, Info, X } from 'lucide-react';
import audioFeedback from '@/Services/AudioFeedbackService';

export default function ToastContainer() {
    const { props } = usePage();
    const flash = props?.flash || {};
    const [toasts, setToasts] = useState([]);

    useEffect(() => {
        const newToasts = [];
        if (flash.success) {
            newToasts.push({
                id: Date.now() + '-success',
                type: 'success',
                message: flash.success,
            });
            audioFeedback.playSuccess();
        }
        if (flash.error) {
            newToasts.push({
                id: Date.now() + '-error',
                type: 'error',
                message: flash.error,
            });
        }
        if (flash.info) {
            newToasts.push({
                id: Date.now() + '-info',
                type: 'info',
                message: flash.info,
            });
        }

        if (newToasts.length > 0) {
            setToasts((prev) => [...prev, ...newToasts]);
        }
    }, [flash.success, flash.error, flash.info]);

    const removeToast = (id) => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
    };

    useEffect(() => {
        if (toasts.length === 0) return;
        const timer = setTimeout(() => {
            setToasts((prev) => prev.slice(1));
        }, 4500);
        return () => clearTimeout(timer);
    }, [toasts]);

    if (toasts.length === 0) return null;

    return (
        <div className="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm pointer-events-none">
            {toasts.map((toast) => {
                const isSuccess = toast.type === 'success';
                const isError = toast.type === 'error';

                return (
                    <div
                        key={toast.id}
                        className={`pointer-events-auto flex items-start gap-3 p-3.5 rounded-2xl border shadow-xl backdrop-blur-md animate-in slide-in-from-top-4 duration-200 ${
                            isSuccess
                                ? 'bg-emerald-50/95 dark:bg-emerald-950/90 border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-100 shadow-emerald-500/10'
                                : isError
                                ? 'bg-rose-50/95 dark:bg-rose-950/90 border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-100 shadow-rose-500/10'
                                : 'bg-slate-50/95 dark:bg-slate-900/90 border-slate-300 dark:border-slate-800 text-slate-900 dark:text-slate-100 shadow-black/20'
                        }`}
                    >
                        <div className="p-1 rounded-lg flex-shrink-0">
                            {isSuccess ? (
                                <CheckCircle2 className="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            ) : isError ? (
                                <AlertCircle className="w-5 h-5 text-rose-600 dark:text-rose-400" />
                            ) : (
                                <Info className="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            )}
                        </div>

                        <div className="flex-1 text-xs font-medium leading-relaxed pt-0.5">
                            {toast.message}
                        </div>

                        <button
                            type="button"
                            onClick={() => removeToast(toast.id)}
                            className="p-1 rounded-lg opacity-60 hover:opacity-100 transition flex-shrink-0"
                        >
                            <X className="w-3.5 h-3.5" />
                        </button>
                    </div>
                );
            })}
        </div>
    );
}
