    import React from "react";
    import {motion, AnimatePresence} from "framer-motion";
    import {useQuery} from '@tanstack/react-query';

    import api from "../axios/api";
    import {IpLogDetails} from "./logs/IpLogDetails";
    import {SlidePanelProps} from "../types";


    const SlidePanel: React.FC<SlidePanelProps> = ({data, onClose, onDeleteLogDetail, fetchDetailRoute}) => {
        const { data: details = [], isLoading } = useQuery({
            queryKey: ['details', fetchDetailRoute],
            queryFn: async () => {
                if (fetchDetailRoute) {
                    const res = await api.get(fetchDetailRoute);
                    return res.data.data;
                }
                return [];
            },
            enabled: !!fetchDetailRoute, // this disables the query if fetchDetailRoute is null/undefined
            staleTime: Infinity
        });

        return (
            <AnimatePresence>
                {data && (
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
                                    duration: .05
                                }
                            }}
                            exit={{
                                x: '100%',
                                opacity: 0,
                                transition: {
                                    duration: .3
                                }
                            }}
                            className="fixed top-0 right-0 h-full w-[60%] bg-white shadow-lg z-50"
                        >
                            <IpLogDetails onDeleteLogDetail={onDeleteLogDetail}  onClose={onClose} isLoading={isLoading} details={details.length > 1 ? details.length : data} />
                        </motion.div>
                    </>
                )}
            </AnimatePresence>
        );
    };

    export default SlidePanel;
