import React from "react";
import {motion, AnimatePresence} from "framer-motion";

interface SlidePanelProps {
    log: any | null;
    onClose: () => void;
}

const SlidePanel: React.FC<SlidePanelProps> = ({log, onClose}) => {
    return (
        <AnimatePresence>
            {log && (
                <>
                    {/* Overlay */}
                    <motion.div
                        className="fixed inset-0 bg-[#0000005c] bg-opacity-30 z-15"
                        initial={{opacity: 0}}
                        animate={{opacity: 1}}
                        exit={{opacity: 0}}
                        transition={{duration: .7}} // slower fade
                        onClick={onClose}
                    />

                    {/* Panel */}
                    <motion.div
                        key="slide-panel"
                        initial={{x: '100%', opacity: 0}}
                        animate={{
                            x: 0,
                            opacity: 1,
                            transition: {
                                opacity: {ease: "spring"},
                                duration: .1
                            }
                        }}
                        exit={{
                            x: '100%',
                            opacity: 0,
                            transition: {
                                duration: .3
                            }
                        }}
                        className="fixed top-0 right-0 h-full w-[40%] bg-white shadow-lg z-50"
                    >
                        <div className="flex justify-between items-center p-4 border-b">
                            <h2 className="text-lg font-semibold">Log Details</h2>
                            <button
                                className="text-2xl font-bold hover:text-red-500 transition"
                                onClick={onClose}
                                aria-label="Close details panel"
                            >
                                &times;
                            </button>
                        </div>
                        <div className="p-4 space-y-2 text-gray-700 overflow-y-auto h-full">
                            <p><strong>ID:</strong> {log?.ID}</p>
                            <p><strong>IP Address:</strong> {log?.ip_address}</p>
                            <p><strong>Status:</strong> {log?.last_status}</p>
                            <p><strong>Attempts:</strong> {log?.attempts}</p>
                            <p><strong>Created At:</strong> {log?.created_at}</p>
                            <p><strong>Last Updated:</strong> {log?.updated_at}</p>
                        </div>
                    </motion.div>
                </>
            )}
        </AnimatePresence>
    );
};

export default SlidePanel;
