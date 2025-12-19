<?php

namespace App\Services;

interface ImporterInterface
{
    /**
     * Create a new importer instance.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters);

    /**
     * Get the fields for the importer.
     *
     * @return array
     */
    public static function getFields(): array;

    /**
     * Get the default values for the importer fields.
     * This method should return an array of field names and their default values,
     * typically reading from environment variables.
     *
     * @return array
     */
    public static function getDefaultValues(): array;

    /**
     * Import the data.
     *
     * @return array
     */
    public function import(): array;



    /**
     * Get the standardized data structure for mapping.
     * Each importer should convert its data to this standard format.
     *
     * @return array
     */
    public function getStandardizedData(): array;

    /**
     * Get the mapping fields that this importer supports.
     * Returns an array of field names that can be mapped.
     *
     * @return array
     */
    public static function getMappingFields(): array;

    /**
     * Get the display fields for the UI.
     * Returns an array of field names to show in the import table.
     *
     * @return array
     */
    public static function getDisplayFields(): array;

    /**
     * Get the asset classes that should be mapped to currency valuations.
     * Returns an array of asset class names that represent cash/currency assets.
     *
     * @return array
     */
    public static function getCashAssetClasses(): array;
}
