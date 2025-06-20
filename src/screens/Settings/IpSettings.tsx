import React, {forwardRef, useMemo, useState} from "react";
import {RateLimitProps} from "../../types";
import Spinner from "../../components/Spinner";
import {__} from "@wordpress/i18n";
import {CheckBox, Input} from "../../components/forms";
import {useQuery} from "@tanstack/react-query";
import api from "../../axios/api";
import DataTable from "../../components/table/DataTable";
import {BookmarkSimple, CalendarCheck, Key, MapPin, Warning} from "@phosphor-icons/react";
import CopyCell from "../../components/table/CopyCell";
import {createColumnHelper} from "@tanstack/react-table";

const IpSettings = forwardRef((props: RateLimitProps, ref: React.Ref<any>) => {
    const {errors, settings} = props;
    const indexRoute = settings?.Routes?.Index?.value;
    const endpoint = settings?.id;
    const url = `${endpoint}${indexRoute}`;
    const columnHelper = createColumnHelper();

    const {data, isLoading} = useQuery({
        initialData: undefined,
        queryKey: ['ip-settings'],
        queryFn: async () => {
            const res = await api.get(url);
            return res.data.data;
        },
        staleTime: Infinity, // cache forever unless manually invalidate
        enabled: !!url // only run when url is defined
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
        columnHelper.accessor('alias', {
            header: () => (
                <>
                    <BookmarkSimple size={16} weight="bold"/>Alias
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
    return (
        <div className="flex gap-4 justify-around flex-col">
            {isLoading ? (
                <div className="flex items-center justify-center">
                    <Spinner size={18} className="rounded-lg" color="border-primary-light"/>
                </div>
            ) : (
                <>

                    <div className="log-body mt-5">
                        <DataTable data={data} columns={columns} isLoading={isLoading}/>
                    </div>

                </>
            )}

        </div>
    );
})
export default IpSettings;

