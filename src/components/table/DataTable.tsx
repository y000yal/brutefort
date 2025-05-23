import React, { useState } from 'react';
import {
    ColumnDef,
    useReactTable,
    getCoreRowModel,
    getPaginationRowModel,
    getFilteredRowModel,
    flexRender,
} from '@tanstack/react-table';
import {
    Table,
    TableHead,
    TableBody,
    TableRow,
    TableCell,
    TextField,
    Button,
    IconButton,
} from '@mui/material';

interface DataTableProps<T> {
    data: T[];
    columns: ColumnDef<T, any>[];
    enableSearch?: boolean;
    actions?: (row: T) => React.ReactNode;
}

const DataTable = <T extends object>({
                                         data,
                                         columns,
                                         enableSearch = true,
                                         actions,
                                     }: DataTableProps<T>) => {
    const [globalFilter, setGlobalFilter] = useState('');

    const table = useReactTable({
        data,
        columns,
        state: {
            globalFilter,
        },
        onGlobalFilterChange: setGlobalFilter,
        getCoreRowModel: getCoreRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        globalFilterFn: (row, columnId, filterValue) =>
            String(row.getValue(columnId)).toLowerCase().includes(filterValue.toLowerCase()),
    });

    return (
        <div>
            {enableSearch && (
                <TextField
                    label="Search"
                    size="small"
                    variant="outlined"
                    value={globalFilter}
                    onChange={(e) => setGlobalFilter(e.target.value)}
                    style={{ marginBottom: '1rem' }}
                />
            )}

            <Table>
                <TableHead>
                    {table.getHeaderGroups().map(headerGroup => (
                        <TableRow key={headerGroup.id}>
                            {headerGroup.headers.map(header => (
                                <TableCell key={header.id}>
                                    {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                                </TableCell>
                            ))}
                            {actions && <TableCell>Actions</TableCell>}
                        </TableRow>
                    ))}
                </TableHead>
                <TableBody>
                    {table.getRowModel().rows.map(row => (
                        <TableRow key={row.id}>
                            {row.getVisibleCells().map(cell => (
                                <TableCell key={cell.id}>
                                    {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                </TableCell>
                            ))}
                            {actions && <TableCell>{actions(row.original)}</TableCell>}
                        </TableRow>
                    ))}
                </TableBody>
            </Table>

            <div style={{ marginTop: '1rem' }}>
                <Button onClick={() => table.previousPage()} disabled={!table.getCanPreviousPage()}>
                    Previous
                </Button>
                <Button onClick={() => table.nextPage()} disabled={!table.getCanNextPage()}>
                    Next
                </Button>
            </div>
        </div>
    );
};

export default DataTable;
