import React from "react";
import Spinner from "../Spinner";
import { LogDetailsInterface } from "../../types";
import { ClockClockwise, ShieldWarning } from "@phosphor-icons/react";
import {formatDate} from "../../utils";



export const IpLogDetails: React.FC<LogDetailsInterface> = ({ onClose, isLoading = false, details }) => {
    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-full">
                <Spinner />
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

    return (
        <div className="flex flex-col h-full overflow-hidden text-gray-800 text-[15px]">
            {/* Header */}
            <div className="flex justify-between items-center p-4 bg-white">
                <h2 className="text-xl font-semibold">IP Log Details</h2>
                <button
                    className="text-2xl font-bold hover:text-red-500 transition"
                    onClick={onClose}
                    aria-label="Close details panel"
                >
                    &times;
                </button>
            </div>

            {/* Summary */}
            <div className="p-4 bg-gray-50 grid grid-cols-2 gap-y-2 gap-x-6">
                <div><span className="font-medium">ID:</span> {details.ID}</div>
                <div><span className="font-medium">IP Address:</span> {details.ip_address}</div>
                <div>
                    <span className="font-medium">Status:</span>
                    <span className={`ml-1 inline-block log-status ${getStatusClass(details.last_status)}`}>
                        {details.last_status}
                    </span>
                </div>
                <div><span className="font-medium">Total Attempts:</span> {details.attempts}</div>
                <div><span className="font-medium">Created:</span> {formatDate(details.created_at)}</div>
                <div><span className="font-medium">Updated:</span> {formatDate(details.updated_at)}</div>
            </div>

            {/* Log Attempts */}
            <div className="flex-1 overflow-y-auto p-4">
                <h3 className="text-lg font-semibold mb-3">Attempt History</h3>
                <div className="space-y-3">
                    {details.log_details?.map((log, index) => (
                        <div
                            key={index}
                            className="rounded-lg p-4 hover:bg-gray-50 cursor-pointer transition border border-gray-100"
                        >
                            <div className="flex justify-between items-center mb-1">
                                <div className="text-base font-medium">
                                    {log.username || <span className="text-gray-400 italic">Unknown user</span>}
                                </div>
                                <div>
                                    <span className={`log-status ${getStatusClass(log.status)}`}>
                                        {log.status === 'fail' ? 'Failed' : log.status === 'locked' ? 'Locked' : 'Success'}
                                    </span>
                                </div>
                            </div>
                            <div className="text-sm text-gray-600 flex items-center gap-1 mb-1">
                                <ClockClockwise className="w-4 h-4" /> {formatDate(log.attempt_time)}
                            </div>
                            <div className="text-sm text-gray-700">
                                <div className="mb-1"><strong>Browser:</strong> {log.user_agent}</div>
                                {log.is_extended === '1' && log.lockout_until && (
                                    <div className="status-locked flex items-center gap-1 mt-1">
                                        <ShieldWarning className="w-4 h-4" />
                                        Locked until: {formatDate(log.lockout_until)}
                                    </div>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
};
