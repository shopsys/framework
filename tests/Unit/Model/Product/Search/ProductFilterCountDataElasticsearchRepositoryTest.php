<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Search;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchRepository;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Search\AggregationResultToProductFilterCountDataTransformer;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer;
use stdClass;

class ProductFilterCountDataElasticsearchRepositoryTest extends TestCase
{
    private const int FLAG_ID = 7;

    private const int BRAND_ID = 5;

    private const int PARAMETER_ID = 3;

    private const int PARAMETER_VALUE_ID = 30;

    private const int SECOND_PARAMETER_ID = 4;

    private const int SECOND_PARAMETER_VALUE_ID = 40;

    private const string INDEX_NAME = 'product_1';

    public function testCountDataOfCategoryIsLoadedWithOneMultiSearch(): void
    {
        $multipleSearchRepository = $this->createMock(MultipleSearchRepository::class);
        $multipleSearchRepository->expects($this->once())
            ->method('searchIndexedByKey')
            ->with($this->callback($this->assertSearchQueriesOfCategory(...)))
            ->willReturn([
                'absolute_numbers' => $this->createAbsoluteNumbersResponse(
                    [
                        self::FLAG_ID => 2,
                        9 => 1,
                    ],
                    [self::BRAND_ID => 3],
                    4,
                    [self::PARAMETER_ID => [self::PARAMETER_VALUE_ID => 1, 31 => 2]],
                ),
                'flags_plus_numbers' => $this->createAbsoluteNumbersResponse([9 => 6], [], 0),
                'brands_plus_numbers' => $this->createAbsoluteNumbersResponse([], [6 => 4], 0),
                'parameter_plus_numbers_3' => $this->createParameterPlusNumbersResponse([31 => 8]),
                'parameter_plus_numbers_4' => $this->createParameterPlusNumbersResponse([41 => 5]),
            ]);

        $countData = $this->createRepository($multipleSearchRepository)->getProductFilterCountDataInCategory(
            $this->createProductFilterDataWithFlagBrandAndParameterFilter(),
            $this->createFilterQuery()->filterByCategory(11),
        );

        $this->assertSame(4, $countData->countInStock);
        $this->assertSame([9 => 6], $countData->countByFlagId);
        $this->assertSame([6 => 4], $countData->countByBrandId);
        $this->assertSame([self::PARAMETER_ID => [31 => 8], self::SECOND_PARAMETER_ID => [41 => 5]], $countData->countByParameterIdAndValueId);
    }

    public function testCountDataWithoutSelectedFiltersIsTakenFromAbsoluteNumbersOnly(): void
    {
        $multipleSearchRepository = $this->createMock(MultipleSearchRepository::class);
        $multipleSearchRepository->expects($this->once())
            ->method('searchIndexedByKey')
            ->with($this->callback(static fn (array $searchQueriesIndexedByQueryName): bool => array_keys($searchQueriesIndexedByQueryName) === ['absolute_numbers']))
            ->willReturn([
                'absolute_numbers' => $this->createAbsoluteNumbersResponse(
                    [self::FLAG_ID => 2],
                    [self::BRAND_ID => 3],
                    4,
                    [self::PARAMETER_ID => [self::PARAMETER_VALUE_ID => 1]],
                ),
            ]);

        $countData = $this->createRepository($multipleSearchRepository)->getProductFilterCountDataInCategory(
            new ProductFilterData(),
            $this->createFilterQuery()->filterByCategory(11),
        );

        $this->assertSame(4, $countData->countInStock);
        $this->assertSame([self::FLAG_ID => 2], $countData->countByFlagId);
        $this->assertSame([self::BRAND_ID => 3], $countData->countByBrandId);
        $this->assertSame([self::PARAMETER_ID => [self::PARAMETER_VALUE_ID => 1]], $countData->countByParameterIdAndValueId);
    }

    public function testCountDataOfSearchIsLoadedWithOneMultiSearchIgnoringParameters(): void
    {
        $multipleSearchRepository = $this->createMock(MultipleSearchRepository::class);
        $multipleSearchRepository->expects($this->once())
            ->method('searchIndexedByKey')
            ->with($this->callback($this->assertSearchQueriesOfSearch(...)))
            ->willReturn([
                'absolute_numbers' => $this->createAbsoluteNumbersResponse([self::FLAG_ID => 1, 9 => 3], [self::BRAND_ID => 2, 6 => 1], 2),
                'flags_plus_numbers' => $this->createAbsoluteNumbersResponse([9 => 5], [], 0),
                'brands_plus_numbers' => $this->createAbsoluteNumbersResponse([], [6 => 4], 0),
            ]);

        $countData = $this->createRepository($multipleSearchRepository)->getProductFilterCountDataInSearch(
            $this->createProductFilterDataWithFlagBrandAndParameterFilter(),
            $this->createFilterQuery(),
        );

        $this->assertSame(2, $countData->countInStock);
        $this->assertSame([9 => 5], $countData->countByFlagId);
        $this->assertSame([6 => 4], $countData->countByBrandId);
        $this->assertSame([], $countData->countByParameterIdAndValueId);
    }

    /**
     * @param array<string, array{index: string, body: array<string, mixed>}> $searchQueriesIndexedByQueryName
     */
    private function assertSearchQueriesOfCategory(array $searchQueriesIndexedByQueryName): bool
    {
        $this->assertSame(
            ['absolute_numbers', 'flags_plus_numbers', 'brands_plus_numbers', 'parameter_plus_numbers_3', 'parameter_plus_numbers_4'],
            array_keys($searchQueriesIndexedByQueryName),
        );

        $absoluteNumbersBody = $searchQueriesIndexedByQueryName['absolute_numbers']['body'];
        $this->assertSame(self::INDEX_NAME, $searchQueriesIndexedByQueryName['absolute_numbers']['index']);
        $this->assertArrayHasKey('parameters', $absoluteNumbersBody['aggs']);
        $this->assertContains(['terms' => ['flags' => [self::FLAG_ID]]], $absoluteNumbersBody['query']['bool']['filter']);

        $flagsPlusNumbersBody = $searchQueriesIndexedByQueryName['flags_plus_numbers']['body'];
        $this->assertSame(['terms' => ['flags' => [self::FLAG_ID]]], $flagsPlusNumbersBody['query']['bool']['must_not']);
        $this->assertNotContains(['terms' => ['flags' => [self::FLAG_ID]]], $flagsPlusNumbersBody['query']['bool']['filter']);
        $this->assertContains(['terms' => ['brand' => [self::BRAND_ID]]], $flagsPlusNumbersBody['query']['bool']['filter']);
        $this->assertContainsEquals($this->createParameterFilter(self::PARAMETER_ID, self::PARAMETER_VALUE_ID), $flagsPlusNumbersBody['query']['bool']['filter']);
        $this->assertContainsEquals($this->createParameterFilter(self::SECOND_PARAMETER_ID, self::SECOND_PARAMETER_VALUE_ID), $flagsPlusNumbersBody['query']['bool']['filter']);

        $brandsPlusNumbersBody = $searchQueriesIndexedByQueryName['brands_plus_numbers']['body'];
        $this->assertSame(['terms' => ['brand' => [self::BRAND_ID]]], $brandsPlusNumbersBody['query']['bool']['must_not']);
        $this->assertContainsEquals($this->createParameterFilter(self::PARAMETER_ID, self::PARAMETER_VALUE_ID), $brandsPlusNumbersBody['query']['bool']['filter']);
        $this->assertContainsEquals($this->createParameterFilter(self::SECOND_PARAMETER_ID, self::SECOND_PARAMETER_VALUE_ID), $brandsPlusNumbersBody['query']['bool']['filter']);

        $parameterPlusNumbersBody = $searchQueriesIndexedByQueryName['parameter_plus_numbers_3']['body'];
        $this->assertSame(
            self::PARAMETER_ID,
            $parameterPlusNumbersBody['aggs']['parameters']['aggs']['filtered_for_parameter']['filter']['term']['parameters.parameter_id'],
        );
        $this->assertNotContainsEquals($this->createParameterFilter(self::PARAMETER_ID, self::PARAMETER_VALUE_ID), $parameterPlusNumbersBody['query']['bool']['filter']);
        $this->assertContainsEquals($this->createParameterFilter(self::SECOND_PARAMETER_ID, self::SECOND_PARAMETER_VALUE_ID), $parameterPlusNumbersBody['query']['bool']['filter']);
        $this->assertContains(['terms' => ['flags' => [self::FLAG_ID]]], $parameterPlusNumbersBody['query']['bool']['filter']);
        $this->assertContains(['terms' => ['brand' => [self::BRAND_ID]]], $parameterPlusNumbersBody['query']['bool']['filter']);

        return true;
    }

    /**
     * @param array<string, array{index: string, body: array<string, mixed>}> $searchQueriesIndexedByQueryName
     */
    private function assertSearchQueriesOfSearch(array $searchQueriesIndexedByQueryName): bool
    {
        $this->assertSame(['absolute_numbers', 'flags_plus_numbers', 'brands_plus_numbers'], array_keys($searchQueriesIndexedByQueryName));

        foreach ($searchQueriesIndexedByQueryName as $searchQuery) {
            $this->assertNotContainsEquals($this->createParameterFilter(self::PARAMETER_ID, self::PARAMETER_VALUE_ID), $searchQuery['body']['query']['bool']['filter']);
            $this->assertNotContainsEquals($this->createParameterFilter(self::SECOND_PARAMETER_ID, self::SECOND_PARAMETER_VALUE_ID), $searchQuery['body']['query']['bool']['filter']);
        }

        $absoluteNumbersBody = $searchQueriesIndexedByQueryName['absolute_numbers']['body'];
        $this->assertArrayNotHasKey('parameters', $absoluteNumbersBody['aggs']);
        $this->assertContains(['terms' => ['brand' => [self::BRAND_ID]]], $absoluteNumbersBody['query']['bool']['filter']);

        $flagsPlusNumbersBody = $searchQueriesIndexedByQueryName['flags_plus_numbers']['body'];
        $this->assertSame(['terms' => ['flags' => [self::FLAG_ID]]], $flagsPlusNumbersBody['query']['bool']['must_not']);

        $brandsPlusNumbersBody = $searchQueriesIndexedByQueryName['brands_plus_numbers']['body'];
        $this->assertSame(['terms' => ['brand' => [self::BRAND_ID]]], $brandsPlusNumbersBody['query']['bool']['must_not']);
        $this->assertNotContains(['terms' => ['brand' => [self::BRAND_ID]]], $brandsPlusNumbersBody['query']['bool']['filter']);

        return true;
    }

    private function createRepository(
        MultipleSearchRepository $multipleSearchRepository,
    ): ProductFilterCountDataElasticsearchRepository {
        return new ProductFilterCountDataElasticsearchRepository(
            $multipleSearchRepository,
            new ProductFilterDataToQueryTransformer(),
            new AggregationResultToProductFilterCountDataTransformer(),
        );
    }

    private function createProductFilterDataWithFlagBrandAndParameterFilter(): ProductFilterData
    {
        $flag = $this->createStub(Flag::class);
        $flag->method('getId')->willReturn(self::FLAG_ID);

        $brand = $this->createStub(Brand::class);
        $brand->method('getId')->willReturn(self::BRAND_ID);

        $productFilterData = new ProductFilterData();
        $productFilterData->flags = [$flag];
        $productFilterData->brands = [$brand];
        $productFilterData->parameters = [
            $this->createParameterFilterData(self::PARAMETER_ID, self::PARAMETER_VALUE_ID),
            $this->createParameterFilterData(self::SECOND_PARAMETER_ID, self::SECOND_PARAMETER_VALUE_ID),
        ];

        return $productFilterData;
    }

    private function createParameterFilterData(int $parameterId, int $parameterValueId): ParameterFilterData
    {
        $parameter = $this->createStub(Parameter::class);
        $parameter->method('getId')->willReturn($parameterId);
        $parameter->method('isSlider')->willReturn(false);

        $parameterValue = $this->createStub(ParameterValue::class);
        $parameterValue->method('getId')->willReturn($parameterValueId);

        $parameterFilterData = new ParameterFilterData();
        $parameterFilterData->parameter = $parameter;
        $parameterFilterData->values = [$parameterValue];

        return $parameterFilterData;
    }

    /**
     * @return array<string, mixed>
     */
    private function createParameterFilter(int $parameterId, int $parameterValueId): array
    {
        return [
            'nested' => [
                'path' => 'parameters',
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_all' => new stdClass(),
                        ],
                        'filter' => [
                            ['term' => ['parameters.parameter_id' => $parameterId]],
                            ['terms' => ['parameters.parameter_value_id' => [$parameterValueId]]],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function createFilterQuery(): FilterQuery
    {
        return new FilterQuery(self::INDEX_NAME, PricingSetting::PRICE_TYPE_WITH_VAT);
    }

    /**
     * @param array<int, int> $countByFlagId
     * @param array<int, int> $countByBrandId
     * @param array<int, array<int, int>> $countByParameterIdAndValueId
     * @return array<string, mixed>
     */
    private function createAbsoluteNumbersResponse(
        array $countByFlagId,
        array $countByBrandId,
        int $countInStock,
        array $countByParameterIdAndValueId = [],
    ): array {
        $parameterBuckets = [];

        foreach ($countByParameterIdAndValueId as $parameterId => $countByValueId) {
            $parameterBuckets[] = [
                'key' => $parameterId,
                'by_value' => ['buckets' => $this->createBuckets($countByValueId)],
            ];
        }

        return [
            'aggregations' => [
                'flags' => ['buckets' => $this->createBuckets($countByFlagId)],
                'brands' => ['buckets' => $this->createBuckets($countByBrandId)],
                'stock' => ['doc_count' => $countInStock],
                'parameters' => ['by_parameters' => ['buckets' => $parameterBuckets]],
            ],
        ];
    }

    /**
     * @param array<int, int> $countByValueId
     * @return array<string, mixed>
     */
    private function createParameterPlusNumbersResponse(array $countByValueId): array
    {
        return [
            'aggregations' => [
                'parameters' => [
                    'filtered_for_parameter' => [
                        'by_parameters' => [
                            'buckets' => [
                                ['by_value' => ['buckets' => $this->createBuckets($countByValueId)]],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<int, int> $countByKey
     * @return array<int, array{key: int, doc_count: int}>
     */
    private function createBuckets(array $countByKey): array
    {
        $buckets = [];

        foreach ($countByKey as $key => $count) {
            $buckets[] = ['key' => $key, 'doc_count' => $count];
        }

        return $buckets;
    }
}
