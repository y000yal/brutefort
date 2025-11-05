import React, {useState, useRef} from "react";
import Spinner from "../Spinner";
import {LogDetailsInterface} from "../../types";
import {
    ArrowClockwise,
    ClockClockwise,
    DotsThreeOutline,
    Pencil,
    PencilLine,
    PencilSimple,
    ShieldWarning,
    Trash,
    X
} from "@phosphor-icons/react";
import {formatDate, showToast} from "../../utils";
import {__} from "@wordpress/i18n";
import api from "../../axios/api";
import {CONSTANTS} from "../../constants";
import {AnimatePresence, motion} from "framer-motion";
import {useQueryClient} from "@tanstack/react-query";

export const IpLogDetails: React.FC<LogDetailsInterface> = ({onClose, onDeleteLogDetail, isLoading = false, isFetching = false, details, refetch}) => {
    const [isActionPillActive, setIsActionPillActive] = useState(false);
    const [showConfirmModal, setShowConfirmModal] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);
    const [logDetailId, setLogDetailId] = useState(0);
    const [localDetails, setLocalDetails] = useState(details);
    const deletedCountRef = useRef(0); // Track how many items we've deleted locally
    const originalAttemptsRef = useRef(details?.attempts || 0); // Track original attempts value
    const queryClient = useQueryClient();

    // Update local details when details prop changes
    React.useEffect(() => {
        if (details) {
            // Update original attempts reference when new data comes in
            originalAttemptsRef.current = details.attempts || 0;
            // Use server data directly - server is the source of truth
            // If we have pending local decrements, they'll be reset after refetch
            setLocalDetails(details);
        }
    }, [details]);

    if (isLoading || isFetching) {
        return (
            <div className="flex items-center justify-center h-full">
                <Spinner/>
            </div>
        );
    }

    // Use localDetails for display, fallback to details
    const displayDetails = localDetails || details;
    if (!displayDetails) return null;

    const getStatusClass = (status: string) => {
        switch (status) {
            case 'fail':
                return 'status-fail';
            case 'locked':
                return 'status-locked';
            case 'success':
                return 'status-success';
            default:
                return 'status-fail';
        }
    };
    const logRoute = CONSTANTS.LOG_ROUTES.log_details;
    const handleDeleteLog = (logDetailId: number) => {
        if (!logDetailId || logDetailId === 0) {
            console.error('Invalid logDetailId:', logDetailId);
            return;
        }
        setIsDeleting(true);

        const deleteUrl = `${logRoute}/${logDetailId}/delete`;
        console.log('Deleting log detail:', deleteUrl);
        
        api.delete(deleteUrl)
            .then((res) => {
                if (res.status === 200) {
                    showToast(
                        res?.data?.data?.message || res?.data?.message || __("Log deleted successfully.", "brutefort"),
                        { type: "success" }
                    );
                    
                    // Update local state - remove deleted log detail and optimistically decrement attempts
                    if (displayDetails && displayDetails.log_details) {
                        const updatedLogDetails = displayDetails.log_details.filter(
                            (log: any) => (log.log_details_id || log.ID) !== logDetailId
                        );
                        deletedCountRef.current += 1; // Track for optimistic update
                        const updatedDetails = {
                            ...displayDetails,
                            log_details: updatedLogDetails,
                            attempts: Math.max(0, (displayDetails.attempts || 0) - 1)
                        };
                        setLocalDetails(updatedDetails);
                    }
                    
                    // Update parent component state
                    if (onDeleteLogDetail) {
                        onDeleteLogDetail(logDetailId);
                    }
                    // Invalidate logs query to update the main logs table
                    queryClient.invalidateQueries({ queryKey: ['logs'] });
                    // Refetch details to get updated data from server
                    if (refetch) {
                        const refetchPromise = refetch();
                        if (refetchPromise && typeof refetchPromise.then === 'function') {
                            refetchPromise.then(() => {
                                // Reset deleted count after successful refetch since server is source of truth
                                deletedCountRef.current = 0;
                            });
                        } else {
                            // If refetch doesn't return a promise, reset immediately
                            deletedCountRef.current = 0;
                        }
                    }
                }
            })
            .catch((error) => {
                showToast(
                    error?.response?.data?.message || __("Failed to delete log detail.", "brutefort"),
                    { type: "error" }
                );
            })
            .finally(() => {
                setIsDeleting(false);
                setShowConfirmModal(false);
            });
    };

    const handleSelectedLog = (log_details_id: number) => {
        console.log('Selected log detail ID:', log_details_id);
        if (!log_details_id || log_details_id === 0) {
            console.error('Invalid log_details_id:', log_details_id);
            return;
        }
        setShowConfirmModal(true);
        setLogDetailId(log_details_id);
    }
    return (
        <>
            <div
                className="dark:bg-gray-800 dark:text-white flex flex-col h-full overflow-hidden mt-6 text-gray-800 text-[14px]">
                {/* Header */}
                <div className="flex justify-between items-center p-4 dark:bg-gray-800 dark:text-white bg-white">
                <span className="text-lg flex gap-2 font-bold">

                    Log Details for {displayDetails.ip_address}
                    {displayDetails.is_whitelisted && (
                        <span className={`text-sm inline-block log-type-whitelist`}>
                            {__('WhiteListed', 'brutefort')}
                        </span>
                    )}
                </span>
                    <div className="flex gap-2 items-center">
                        {refetch && (
                            <button
                                onClick={() => refetch()}
                                disabled={isFetching}
                                className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 flex items-center justify-center cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                aria-label="Refresh log details"
                                title={__('Refresh', 'brutefort')}
                            >
                                {isFetching ? (
                                    <Spinner size={20} className="text-gray-600 dark:text-gray-300" />
                                ) : (
                                    <ArrowClockwise className="w-5 h-5 text-gray-600 dark:text-gray-300" />
                                )}
                            </button>
                        )}
                        <button
                            onClick={onClose}
                            className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 flex items-center justify-center cursor-pointer"
                            aria-label="Close details panel"
                            title={__('Close', 'brutefort')}
                        >
                            <X className="w-5 h-5 text-gray-600 dark:text-gray-300 hover:text-red-500 dark:hover:text-red-400" />
                        </button>
                    </div>
                </div>

                {/* Summary */}
                <div className="p-6 dark:bg-gray-800 dark:text-white grid grid-cols-2 items-center gap-y-2 gap-x-6">
                    <div><span className="font-medium">ID:</span> {displayDetails.ID}</div>
                    <div><span className="font-medium">IP Address:</span> {displayDetails.ip_address}</div>
                    <div className="flex gap-2 items-center">
                        <span className="font-medium">Status:</span>
                        <span className={`log-status ${getStatusClass(displayDetails.last_status)}`}>
                        {displayDetails.last_status ? displayDetails.last_status.charAt(0).toUpperCase() + displayDetails.last_status.slice(1).toLowerCase() : displayDetails.last_status}
                    </span>
                        {/* <Pencil className="cursor-pointer" size={18}/> */}
                    </div>
                    <div><span className="font-medium">Total Attempts:</span> {displayDetails.attempts}</div>
                    <div><span className="font-medium">Created:</span> {formatDate(displayDetails.created_at)}</div>
                    <div><span className="font-medium">Updated:</span> {formatDate(displayDetails.updated_at)}</div>

                </div>

                {/* Log Attempts */}
                <div className="flex-1 overflow-y-auto p-4 ">
                    <span className="text-lg flex gap-2 font-bold mb-3">Attempt History</span>
                    <div className="space-y-3">
                        {displayDetails.log_details?.map((log: any, index: number) => (
                            <div
                                key={index}
                                className="rounded-lg p-4 dark:hover:bg-gray-900 hover:bg-gray-50 transition border border-gray-100"
                            >
                                <div className="flex dark:text-white justify-between items-center mb-1">
                                    <div className="dark:text-white font-medium">
                                        {log.username ||
                                            <span className="text-gray-400 dark:text-white italic">Unknown user</span>}
                                    </div>
                                    <div className="flex gap-2 items-center">
                                    <span className={`log-status ${getStatusClass(log.status)} text-[12px]`}>
                                        {log.status === 'fail' ? 'Failed' : log.status === 'locked' ? 'Locked' : 'Success'}
                                    </span>
                                        <Trash
                                            onClick={() => handleSelectedLog(log.log_details_id || log.ID)}
                                            className="text-gray-500 hover:text-red-700 transition-colors duration-100 cursor-pointer"/>
                                    </div>
                                </div>
                                <div className="dark:text-white text-sm text-gray-600 flex items-center gap-1 mb-1">
                                    <ClockClockwise className="w-4 h-4"/> {formatDate(log.attempt_time)}
                                </div>
                                <div className="dark:text-white text-sm text-gray-700">
                                    <div className="mb-1"><strong>Browser:</strong> {log.user_agent}</div>
                                    {log.is_extended === '1' && log.lockout_until && (
                                        <div
                                            className="status-locked p-2 flex text-white rounded-[8px] items-center gap-1 mt-1">
                                            <ShieldWarning className="text-[#e76a70] w-4 h-4"/>
                                            Locked until: {formatDate(log.lockout_until)}
                                        </div>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            <AnimatePresence>
                {showConfirmModal && (
                    <motion.div
                        initial={{opacity: 0, y: 20}}
                        animate={{opacity: 1, y: 0}}
                        exit={{opacity: 0}}
                        transition={{
                            duration: 0.2,
                            scale: {type: "spring", visualDuration: 0.2, bounce: 0.2},
                        }}
                        className="fixed left-[160px] inset-0 flex items-center justify-center z-50 bg-black/40"
                        id="confirm-delete"
                    >
                        <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-sm w-full">
                            <h2 className="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                                Confirm Delete
                            </h2>
                            <p className="text-sm text-gray-600 dark:text-gray-300 mb-6">
                                {__(
                                    "Are you sure you want to delete the selected log?",
                                    "brutefort"
                                )}
                            </p>
                            <div className="flex justify-end gap-2">
                                <button
                                    className="px-4 py-1 text-sm rounded border cursor-pointer border-gray-300 hover:bg-white-400 dark:border-gray-600"
                                    onClick={() => setShowConfirmModal(false)}
                                >
                                    {__("Cancel", "brutefort")}
                                </button>
                                <button
                                    className="px-4 py-1 text-sm text-white bg-red-500 hover:bg-red-600 rounded cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                    onClick={() => {
                                        console.log('Delete button clicked, logDetailId:', logDetailId);
                                        handleDeleteLog(logDetailId);
                                    }}
                                    disabled={isDeleting || !logDetailId || logDetailId === 0}
                                >
                                    {__("Delete", "brutefort")}
                                </button>
                                {isDeleting && (
                                    <Spinner
                                        size={18}
                                        className="rounded-lg"
                                        color="border-primary-light"
                                    />
                                )}
                            </div>
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>
        </>
    );
};
