import React from "react";
import { Column } from "@tanstack/react-table";

interface ColumnFilterProps<T> {
  column: Column<T, unknown>;
}

const ColumnFilter = <T,>({ column }: ColumnFilterProps<T>) => {
  const columnFilterValue = column.getFilterValue();
  const meta = column.columnDef.meta as {
    filterType?: "text" | "dropdown";
    filterOptions?: string[];
  };

  if (meta?.filterType === "dropdown" && meta?.filterOptions?.length) {
    return (
      <select
        value={columnFilterValue ?? ""}
        onChange={(e) => column.setFilterValue(e.target.value || undefined)}
        className="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded px-2 py-1 text-sm w-full"
      >
        <option value="">All</option>
        {meta.filterOptions.map((opt) => (
          <option key={opt} value={opt}>
            {opt}
          </option>
        ))}
      </select>
    );
  }
  if (meta?.filterType === "date") {
    return (
      <input
        type="date"
        value={columnFilterValue ?? ""}
        onChange={(e) => column.setFilterValue(e.target.value || undefined)}
        className="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded px-2 py-1 text-sm w-full"
      />
    );
  }
  return (
    <input
      type="text"
      className="border border-gray-300 rounded px-2 py-1 text-sm w-full"
      value={(columnFilterValue ?? "") as string}
      onChange={(e) => column.setFilterValue(e.target.value)}
      placeholder="Search..."
    />
  );
};

export default ColumnFilter;
