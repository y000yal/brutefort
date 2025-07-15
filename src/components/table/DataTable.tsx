import React, { useEffect } from "react";
import {
  useReactTable,
  getCoreRowModel,
  getPaginationRowModel,
  getFilteredRowModel,
  flexRender,
  ColumnDef,
  Row,
  SortingState,
  getSortedRowModel,
} from "@tanstack/react-table";
import Spinner from "../Spinner";
import ColumnFilter from "./ColumnFilter";
import { ArrowDown, ArrowUp, Note, Trash } from "@phosphor-icons/react";
import { __ } from "@wordpress/i18n";

interface DataTableProps<T> {
  data: T[];
  columns: ColumnDef<T, any>[];
  isLoading?: boolean;
  onRowClick?: (row: Row<T>) => void;
  onSelectionChange?: (selectedData: T[]) => void; // ✅ New
}

const DataTable = <T,>({
  data,
  columns,
  isLoading = false,
  onRowClick,
  onSelectionChange,
}: DataTableProps<T>) => {
  const [columnFilters, setColumnFilters] = React.useState([]);
  const [sorting, setSorting] = React.useState<SortingState>([]);

  const table = useReactTable({
    data,
    columns,
    state: {
      columnFilters,
      sorting,
    },
    onColumnFiltersChange: setColumnFilters,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: setSorting,
    getSortedRowModel: getSortedRowModel(),
  });
  useEffect(() => {
    const selected = table
      .getSelectedRowModel()
      .rows.map((row) => row.original);
    onSelectionChange?.(selected);
  }, [table.options.state.rowSelection]);
  
  if (isLoading)
    return (
      <div className="flex items-center justify-center">
        <Spinner />
      </div>
    );

  return (
    <div className="rounded-lg overflow-hidden dark:bg-gray-800 border border-gray-200 shadow-sm">
      <table className="w-full table-auto dark:bg-gray-800 text-gray-500 border border-gray-200">
        <thead className="bg-gray-100 dark:bg-gray-800">
          {table.getHeaderGroups().map((headerGroup) => (
            <tr key={headerGroup.id}>
              {headerGroup.headers.map((header) => (
                <th key={header.id} className="text-left p-2 ">
                  {!header.isPlaceholder && (
                    <div className="flex flex-col gap-1">
                      <span
                        className="text-sm font-semibold text-gray-700 dark:text-white flex gap-1 items-center cursor-pointer select-none"
                        onClick={header.column.getToggleSortingHandler()}
                      >
                        {flexRender(
                          header.column.columnDef.header,
                          header.getContext()
                        )}
                        {header.column.getIsSorted() === "asc" && (
                          <ArrowUp size={14} />
                        )}
                        {header.column.getIsSorted() === "desc" && (
                          <ArrowDown size={14} />
                        )}
                      </span>
                      {header.column.getCanFilter() && (
                        <div>
                          <ColumnFilter column={header.column} />
                        </div>
                      )}
                    </div>
                  )}
                </th>
              ))}
            </tr>
          ))}
        </thead>
        <tbody>
          {table.getRowModel().rows.map((row) => (
            <tr
              key={row.id}
              className="hover:bg-gray-50 dark:hover:bg-gray-900 font-medium cursor-pointer h-[70px]"
              onClick={() => onRowClick?.(row)}
            >
              {row.getVisibleCells().map((cell) => (
                <td key={cell.id} className="p-2">
                  {flexRender(cell.column.columnDef.cell, cell.getContext())}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
      <div className="p-2 flex items-center justify-between border-t border-gray-200 dark:bg-gray-800 dark:text-white bg-gray-50">
        <div className="flex items-center dark:text-white gap-2">
          <button
            className="px-3 py-1 bg-gray-200 dark:bg-gray-700 dark:text-white rounded disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            onClick={() => table.previousPage()}
            disabled={!table.getCanPreviousPage()}
          >
            Prev
          </button>
          <span className="dark:text-white text-sm text-gray-700">
            Page <strong>{table.getState().pagination.pageIndex + 1}</strong> of{" "}
            <strong>{table.getPageCount()}</strong>
          </span>
          <button
            className="dark:text-white px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            onClick={() => table.nextPage()}
            disabled={!table.getCanNextPage()}
          >
            Next
          </button>
        </div>

        <div>
          <select
            className="border border-gray-300 text-sm px-2 py-1 rounded"
            value={table.getState().pagination.pageSize}
            onChange={(e) => {
              table.setPageSize(Number(e.target.value));
            }}
          >
            {[10, 20, 30, 50].map((pageSize) => (
              <option key={pageSize} value={pageSize}>
                Show {pageSize}
              </option>
            ))}
          </select>
        </div>
      </div>
    </div>
  );
};

export default DataTable;
