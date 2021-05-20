<?php

namespace Poppy\System\Classes\Grid\Concerns;

use Illuminate\Support\Collection;
use Poppy\System\Classes\Grid;
use Poppy\System\Classes\Grid\Column;
use Poppy\System\Classes\Grid\Tools\ColumnSelector;

trait CanHidesColumns
{
    /**
     * Default columns be hidden.
     *
     * @var array
     */
    public $hiddenColumns = [];

    /**
     * Remove column selector on grid.
     *
     * @param bool $disable
     *
     * @return Grid|mixed
     */
    public function disableColumnSelector(bool $disable = true): bool
    {
        return $this->option('show_column_selector', !$disable);
    }

    /**
     * @return bool
     */
    public function showColumnSelector(): bool
    {
        return $this->option('show_column_selector');
    }

    /**
     * Setting default shown columns on grid.
     *
     * @param array|string $columns
     *
     * @return $this
     */
    public function hideColumns($columns): self
    {
        if (func_num_args()) {
            $columns = (array) $columns;
        }
        else {
            $columns = func_get_args();
        }

        $this->hiddenColumns = array_merge($this->hiddenColumns, $columns);

        return $this;
    }

    /**
     * Get all visible column instances.
     *
     * @return Column[]|Collection
     */
    public function visibleColumns(): Collection
    {
        $visible = $this->getVisibleColumnsFromQuery();

        if (empty($visible)) {
            return $this->columns;
        }

        array_push($visible, Grid\Column::NAME_SELECTOR, Grid\Column::NAME_ACTION);

        return $this->columns->filter(function (Grid\Column $column) use ($visible) {
            return in_array($column->name, $visible);
        });
    }

    /**
     * Get all visible column names.
     *
     * @return array
     */
    public function visibleColumnNames(): array
    {
        $visible = $this->getVisibleColumnsFromQuery();

        if (empty($visible)) {
            return $this->columnNames;
        }

        array_push($visible, Grid\Column::NAME_SELECTOR, Grid\Column::NAME_ACTION);

        return collect($this->columnNames)->filter(function ($column) use ($visible) {
            return in_array($column, $visible);
        })->toArray();
    }

    /**
     * Get default visible column names.
     *
     * @return array
     */
    public function getDefaultVisibleColumnNames(): array
    {
        return array_values(
            array_diff(
                $this->columnNames,
                $this->hiddenColumns,
                [Grid\Column::NAME_SELECTOR, Grid\Column::NAME_ACTION]
            )
        );
    }

    /**
     * Get visible columns from request query.
     *
     * @return array
     */
    protected function getVisibleColumnsFromQuery()
    {
        $columns = explode(',', request(ColumnSelector::SELECT_COLUMN_NAME));

        return array_filter($columns) ?:
            array_values(array_diff($this->columnNames, $this->hiddenColumns));
    }
}
