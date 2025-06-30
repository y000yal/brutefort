import React from "react";
import { Column } from "@tanstack/react-table";

interface ColumnFilterProps<T> {
  column: Column<T, unknown>;
  placeholder?: string;
}

const ColumnFilter = <T,>({ column, placeholder = "Search..." }: ColumnFilterProps<T>) => {
  const columnFilterValue = column.getFilterValue();

  return (
    <input
      type="text"
      className="border border-gray-300 rounded px-2 py-1 text-sm w-full"
      value={(columnFilterValue ?? "") as string}
      onChange={(e) => column.setFilterValue(e.target.value)}
      placeholder={placeholder}
    />
  );
};

export default ColumnFilter;
