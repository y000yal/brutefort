import React, {useState} from "react";
import Spinner from "../Spinner";
import {LogDetailsInterface} from "../../types";
import {
    ClockClockwise,
    DotsThreeOutline,
    Pencil,
    PencilLine,
    PencilSimple,
    ShieldWarning,
    Trash
} from "@phosphor-icons/react";
import {formatDate, showToast} from "../../utils";
import {__} from "@wordpress/i18n";
import api from "../../axios/api";
import {CONSTANTS} from "../../constants";
import {AnimatePresence, motion} from "framer-motion";
import {useQueryClient} from "@tanstack/react-query";

export const IpLogDetails: React.FC<LogDetailsInterface> = ({onClose, onDeleteLogDetail, isLoading = false, details}) => {
    const [isActionPillActive, setIsActionPillActive] = useState(false);
    const [showConfirmModal, setShowConfirmModal] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);
    const [logDetailId, setLogDetailId] = useState(0);
    const queryClient = useQueryClient();

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-full">
                <Spinner/>
            </div>
        );
    }

    if (!details) return null;

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
        if (!logDetailId) return;
        setIsDeleting(true);

        api.delete(`${logRoute}/${logDetailId}/delete`)
            .then((res) => {
                if (res.status === 200) {
                    showToast(
                        res?.data?.data?.message || __("Log deleted successfully.", "brutefort"),
                        { type: "success" }
                    );
                    onDeleteLogDetail(logDetailId);
                }
            })
            .finally(() => {
                setIsDeleting(false);
                setShowConfirmModal(false);
            });
    };

    const handleSelectedLog = (log_details_id: number) => {
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

                    Log Details for {details.ip_address}
                    {details.is_whitelisted && (
                        <span className={`text-sm inline-block log-type-whitelist`}>
                            {__('WhiteListed', 'brutefort')}
                        </span>
                    )}
                </span>
                    <span
                        className="text-[20px] font-bold hover:text-red-500 transition cursor-pointer"
                        onClick={onClose}
                        aria-label="Close details panel"
                    >
                    &times;
                </span>
                </div>

                {/* Summary */}
                <div className="p-6 dark:bg-gray-800 dark:text-white grid grid-cols-2 items-center gap-y-2 gap-x-6">
                    <div><span className="font-medium">ID:</span> {details.ID}</div>
                    <div><span className="font-medium">IP Address:</span> {details.ip_address}</div>
                    <div className="flex gap-2 items-center">
                        <span className="font-medium">Status:</span>
                        <span className={`log-status ${getStatusClass(details.last_status)}`}>
                        {details.last_status}
                    </span>
                        {/* <Pencil className="cursor-pointer" size={18}/> */}
                    </div>
                    <div><span className="font-medium">Total Attempts:</span> {details.attempts}</div>
                    <div><span className="font-medium">Created:</span> {formatDate(details.created_at)}</div>
                    <div><span className="font-medium">Updated:</span> {formatDate(details.updated_at)}</div>

                </div>

                {/* Log Attempts */}
                <div className="flex-1 overflow-y-auto p-4 ">
                    <span className="text-lg flex gap-2 font-bold mb-3">Attempt History</span>
                    <div className="space-y-3">
                        {details.log_details?.map((log, index) => (
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
                                            onClick={() => handleSelectedLog(log.log_details_id)}
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
                                    className="px-4 py-1 text-sm text-white bg-red-500 hover:bg-red-600 rounded cursor-pointer"
                                    onClick={() => handleDeleteLog(logDetailId)}
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
