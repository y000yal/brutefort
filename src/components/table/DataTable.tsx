import React from 'react';
import {
    useReactTable,
    getCoreRowModel,
    getPaginationRowModel,
    flexRender,
    ColumnDef
} from '@tanstack/react-table';
import Spinner from "../Spinner";

interface DataTableProps<T> {
    data: T[];
    columns: ColumnDef<T, any>[];
    isLoading?: boolean;
    onRowClick?: (row: Row<T>) => void;
}

const DataTable = <T, >({data, columns, isLoading = false, onRowClick}: DataTableProps<T>) => {
    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
    });

    if (isLoading) return (
        <div className="flex items-center justify-center">
            <Spinner/>
        </div>
    );

    return (
        <div className="rounded-lg overflow-hidden border border-gray-200 shadow-sm">
            <table className="w-full table-auto text-gray-500 border border-gray-200">
                <thead className="bg-gray-100 h-[50px]">
                {table.getHeaderGroups().map(headerGroup => (
                    <tr key={headerGroup.id}>
                        {headerGroup.headers.map(header => (
                            <th key={header.id} className="text-left p-2">
                                {!header.isPlaceholder && (
                                    <div className="flex items-center gap-1">
                                        {flexRender(
                                            header.column.columnDef.header,
                                            header.getContext()
                                        )}
                                    </div>
                                )}
                            </th>
                        ))}
                    </tr>
                ))}
                </thead>
                <tbody>
                {table.getRowModel().rows.map(row => (
                    <tr key={row.id} className="hover:bg-gray-50 font-medium cursor-pointer h-[70px]"
                        onClick={() => onRowClick?.(row)}>
                        {row.getVisibleCells().map(cell => (
                            <td key={cell.id} className="p-2">
                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                            </td>
                        ))}
                    </tr>
                ))}
                </tbody>
            </table>
        </div>
    );
};

export default DataTable;
