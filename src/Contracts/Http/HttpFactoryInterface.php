<?php namespace Neomerx\JsonApi\Contracts\Http;

/**
 * Copyright 2015-2017 info@neomerx.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\ParametersAnalyzerInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\SortParameterInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\AcceptHeaderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\AcceptMediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeadersCheckerInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\SupportedExtensionsInterface;
use Neomerx\JsonApi\Contracts\Http\Query\QueryCheckerInterface;
use Neomerx\JsonApi\Contracts\Http\Query\QueryParametersParserInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;

/**
 * @package Neomerx\JsonApi
 */
interface HttpFactoryInterface
{
    /**
     * Create parameter analyzer.
     *
     * @param EncodingParametersInterface $parameters
     * @param ContainerInterface          $container
     *
     * @return ParametersAnalyzerInterface
     */
    public function createParametersAnalyzer(
        EncodingParametersInterface $parameters,
        ContainerInterface $container
    ): ParametersAnalyzerInterface;

    /**
     * Create media type.
     *
     * @param string $type
     * @param string $subType
     * @param array<string,string>|null $parameters
     *
     * @return MediaTypeInterface
     */
    public function createMediaType(string $type, string $subType, array $parameters = null): MediaTypeInterface;

    /**
     * Create parameters.
     *
     * @param string[]|null                 $includePaths
     * @param array|null                    $fieldSets
     * @param SortParameterInterface[]|null $sortParameters
     * @param array|null                    $pagingParameters
     * @param array|null                    $filteringParameters
     * @param array|null                    $unrecognizedParams
     *
     * @return EncodingParametersInterface
     */
    public function createQueryParameters(
        array $includePaths = null,
        array $fieldSets = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null,
        array $unrecognizedParams = null
    ): EncodingParametersInterface;

    /**
     * @param string                $method
     * @param AcceptHeaderInterface $accept
     * @param HeaderInterface       $contentType
     *
     * @return HeaderParametersInterface
     */
    public function createHeaderParameters(
        string $method,
        AcceptHeaderInterface $accept,
        HeaderInterface $contentType
    ): HeaderParametersInterface;

    /**
     * @param string                $method
     * @param AcceptHeaderInterface $accept
     *
     * @return HeaderParametersInterface
     */
    public function createNoContentHeaderParameters(
        string $method,
        AcceptHeaderInterface $accept
    ): HeaderParametersInterface;

    /**
     * Create parameters parser.
     *
     * @return QueryParametersParserInterface
     */
    public function createQueryParametersParser(): QueryParametersParserInterface;

    /**
     * Create parameters parser.
     *
     * @return HeaderParametersParserInterface
     */
    public function createHeaderParametersParser(): HeaderParametersParserInterface;

    /**
     * Create sort parameter.
     *
     * @param string $sortField
     * @param bool   $isAscending
     *
     * @return SortParameterInterface
     */
    public function createSortParam(string $sortField, bool $isAscending): SortParameterInterface;

    /**
     * Create supported extensions.
     *
     * @param string $extensions
     *
     * @return SupportedExtensionsInterface
     */
    public function createSupportedExtensions(
        string $extensions = MediaTypeInterface::NO_EXT
    ): SupportedExtensionsInterface;

    /**
     * Create media type for Accept HTTP header.
     *
     * @param int    $position
     * @param string $type
     * @param string $subType
     * @param array<string,string>|null $parameters
     * @param float  $quality
     * @param array<string,string>|null $extensions
     *
     * @return AcceptMediaTypeInterface
     */
    public function createAcceptMediaType(
        int $position,
        string $type,
        string $subType,
        array $parameters = null,
        float $quality = 1.0,
        array $extensions = null
    ): AcceptMediaTypeInterface;

    /**
     * Create Accept HTTP header.
     *
     * @param AcceptMediaTypeInterface[] $unsortedMediaTypes
     *
     * @return AcceptHeaderInterface
     */
    public function createAcceptHeader(array $unsortedMediaTypes): AcceptHeaderInterface;

    /**
     * Create header parameters checker.
     *
     * @param CodecMatcherInterface $codecMatcher
     *
     * @return HeadersCheckerInterface
     */
    public function createHeadersChecker(CodecMatcherInterface $codecMatcher): HeadersCheckerInterface;

    /**
     * Create query parameters checker.
     *
     * @param bool|false $allowUnrecognized
     * @param array|null $includePaths
     * @param array|null $fieldSetTypes
     * @param array|null $sortParameters
     * @param array|null $pagingParameters
     * @param array|null $filteringParameters
     *
     * @return QueryCheckerInterface
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function createQueryChecker(
        bool $allowUnrecognized = false,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ): QueryCheckerInterface;
}
