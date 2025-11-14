import React, {useMemo, useState} from "react";
import {useQuery, useQueryClient} from '@tanstack/react-query';
import api from "../axios/api";
import {CONSTANTS} from "../constants";
import {
    createColumnHelper
} from '@tanstack/react-table';
import {Input} from "../components/forms";
import {
    ArrowClockwise,
    BookmarkSimple,
    CalendarCheck, Copy, Key,
    MagnifyingGlass,
    MapPin,
    TextAa,
    Warning,
    WarningCircle
} from "@phosphor-icons/react";

import DataTable from "../components/table/DataTable";
import CopyCell from "../components/table/CopyCell";
import SlidePanel from "../components/SlidePanel";

const columnHelper = createColumnHelper();

const Logs = () => {
    const fetchRoute = CONSTANTS.LOG_ROUTES.logs;
    const [searchInput, setSearchInput] = useState('');
    const [hasSearchInput, setHasSearchInput] = useState(false);
    const [selectedLog, setSelectedLog] = useState(null); // Selected row for details panel
    const queryClient = useQueryClient();

    const {data = [], isLoading, isFetching, refetch} = useQuery({
        queryKey: ['logs'],
        queryFn: async () => {
            const res = await api.get(fetchRoute);
            return res.data.data;
        },
        staleTime: Infinity,
        enabled: !!fetchRoute
    });

    const columns = useMemo(() => [
        columnHelper.display({
            id: 'select',
            header: ({table}) => (
                <input
                    type="checkbox"
                    checked={table.getIsAllPageRowsSelected()}
                    onChange={table.getToggleAllPageRowsSelectedHandler()}
                />
            ),
            cell: ({row}) => (
                <input
                    type="checkbox"
                    checked={row.getIsSelected()}
                    onChange={row.getToggleSelectedHandler()}
                />
            ),
        }),
        columnHelper.accessor('ID', {
            header: () => (
                <>
                    <Key size={16} weight="bold"/>id
                </>
            ),
            cell: ({getValue}) => <CopyCell value={getValue()}/>
        }),
        columnHelper.accessor('last_status', {
            header: () => (
                <>
                    <BookmarkSimple size={16} weight="bold"/>currentStatus
                </>
            ),
            cell: info => (
                <span className={`capitalize log-status status-${info.getValue()}`}>
                    {info.getValue()}
                </span>
            )
        }),
        columnHelper.accessor('ip_address', {
            header: () => (
                <>
                    <MapPin size={16} weight="bold"/> ipAddress
                </>
            ),
            cell: ({getValue}) => <CopyCell value={getValue()}/>
        }),
        columnHelper.accessor('attempts', {
            header: () => (
                <>
                    <Warning size={16} weight="bold"/> failedAttempts
                </>
            ),
        }),
        columnHelper.accessor('created_at', {
            header: () => (
                <>
                    <CalendarCheck size={16} weight="bold"/> created
                </>
            ),
            cell: info => {
                const rawDate = new Date(info.getValue());

                const formattedDate = rawDate.toLocaleDateString(undefined as string, {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                const formattedTime = rawDate.toLocaleTimeString(undefined as string, {
                    hour: 'numeric',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true,
                    timeZoneName: 'short'
                });

                return (
                    <span className="flex flex-col gap-1 items-start text-sm text-gray-600">
            <span>{formattedDate}</span>
            <span className="text-xs text-gray-400">{formattedTime}</span>
        </span>
                );
            }
        })
    ], []);

    const handleSearchBarChange = (e) => {
        const value = e.target.value;
        setSearchInput(value);
        setHasSearchInput(value.length > 0);
    };

    const handleRefresh = () => {
        refetch();
    };

    const handleDeleteLogDetail = (logDetailId) => {
        setSelectedLog((prev: any) => {
            if (!prev) return prev;
            const updatedLogs = prev.log_details.filter(l => (l.log_details_id || l.ID) !== logDetailId);
            // Decrement attempts count when a log detail is deleted
            const updatedAttempts = Math.max(0, (prev.attempts || 0) - 1);
            if (updatedLogs.length === 0) {
                setSelectedLog(null); // close panel
            }
            return { ...prev, log_details: updatedLogs, attempts: updatedAttempts };
        });
        
        // Invalidate and refetch the logs query to update the table
        queryClient.invalidateQueries({ queryKey: ['logs'] });
    };
    return (
        <>
            <div className="p-4 rounded-lg w-full transition-colors duration-300 gap-4">
                <div className="header flex flex-col gap-4 mb-5">
                    <div className="flex items-center justify-between">
                        <span className="text-2xl font-bold">Logs</span>
                        <button
                            onClick={handleRefresh}
                            disabled={isFetching}
                            className="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            title="Refresh logs"
                        >
                            <ArrowClockwise 
                                size={18} 
                                className={isFetching ? "animate-spin" : ""}
                            />
                            Refresh
                        </button>
                    </div>
                    {/* <div className="search-box relative">
                        <label htmlFor="search-bar">
                            <MagnifyingGlass size={24} className="absolute top-3 left-3 z-10 text-gray-400"/>
                        </label>
                        <Input
                            id="search-bar"
                            className="pl-10 rounded-lg"
                            placeholder="Search by IP"
                            value={searchInput}
                            onChange={handleSearchBarChange}
                        />
                        {hasSearchInput && (
                            <div className="right-search-bar absolute top-2 right-2 flex gap-2">
                                <span className="cursor-pointer search-bar-btn search text-blue-500" onClick={() => {
                                }}>Search</span>
                                <span className="cursor-pointer search-bar-btn text-red-500" onClick={() => {
                                    setSearchInput('');
                                    setHasSearchInput(false);
                                }}>Clear</span>
                            </div>
                        )}
                    </div> */}
                </div>
            </div>
            <div className="log-body mt-5">
                <DataTable data={data} columns={columns} isLoading={isLoading || isFetching} onRowClick={row => setSelectedLog(row.original)}/>
            </div>

            {selectedLog && (
                <SlidePanel 
                    data={selectedLog} 
                    onClose={() => setSelectedLog(null)}  
                    onDeleteLogDetail={handleDeleteLogDetail}
                    fetchDetailRoute={selectedLog?.ID ? `${CONSTANTS.LOG_ROUTES.logs}/${selectedLog.ID}/details` : null}
                />
            )}

        </>

    );
};

export default Logs;
