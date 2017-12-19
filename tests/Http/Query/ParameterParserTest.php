<?php namespace Neomerx\Tests\JsonApi\Http\Query;

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

use Neomerx\JsonApi\Contracts\Http\Query\QueryParametersParserInterface;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Neomerx\JsonApi\Factories\Factory;
use Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ParameterParserTest extends BaseTestCase
{
    /** Query params */
    const QUERY_PARAMS = 'query_params';

    /**
     * @var QueryParametersParserInterface
     */
    private $parser;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->parser = (new Factory())->createQueryParametersParser();
    }

    /**
     * Test parse parameters.
     */
    public function testHeadersWithNoParameters()
    {
        $parameters = $this->parser->parse([]);

        $this->assertNull($parameters->getFieldSets());
        $this->assertNull($parameters->getIncludePaths());
        $this->assertNull($parameters->getSortParameters());
        $this->assertNull($parameters->getFilteringParameters());
        $this->assertNull($parameters->getPaginationParameters());
        $this->assertTrue($parameters->isEmpty());
    }

    /**
     * Test parse parameters.
     */
    public function testHeadersWithNoExtensionsButWithParameters()
    {
        $fieldSets = ['type1' => 'fields1,fields2'];
        $filter    = ['some' => 'filter'];
        $paging    = ['size' => 10, 'offset' => 4];

        $input = [
            'fields'  => $fieldSets,
            'include' => 'author,comments,comments.author',
            'sort'    => '-created,+title,name', // omitting '+' is against spec but in reality frameworks remove it
            'filter'  => $filter,
            'page'    => $paging,
        ];

        $parameters = $this->parser->parse($input);

        $this->assertEquals(['type1' => ['fields1', 'fields2']], $parameters->getFieldSets());
        $this->assertEquals(['author', 'comments', 'comments.author'], $parameters->getIncludePaths());

        $this->assertCount(3, $sortParams = $parameters->getSortParameters());
        $this->assertEquals('created', $sortParams[0]->getField());
        $this->assertEquals('title', $sortParams[1]->getField());
        $this->assertEquals('name', $sortParams[2]->getField());
        $this->assertFalse($sortParams[0]->isAscending());
        $this->assertTrue($sortParams[1]->isAscending());
        $this->assertTrue($sortParams[2]->isAscending());
        $this->assertTrue($sortParams[0]->isDescending());
        $this->assertFalse($sortParams[1]->isDescending());
        $this->assertFalse($sortParams[2]->isDescending());

        $this->assertEquals($filter, $parameters->getFilteringParameters());
        $this->assertEquals($paging, $parameters->getPaginationParameters());
    }

    /**
     * Test that sort parameters can be cast back to a string.
     */
    public function testSortParamsStringConversion()
    {
        $expected   = '-created,title,name';
        $input      = ['sort' => $expected];
        $parameters = $this->parser->parse($input);

        $actual = implode(',', array_map(function ($param) {
            return (string)$param;
        }, $parameters->getSortParameters()));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test miss field in sort params.
     */
    public function testSendIncorrectSortParams1()
    {
        $input = [
            'sort' => '-created,,name',
        ];

        $exception = null;
        try {
            $this->parser->parse($input);
        } catch (JsonApiException $exception) {
        }

        $this->assertNotNull($exception);
        $this->assertNotEmpty($exception->getErrors());
    }

    /**
     * Test miss field in sort params.
     */
    public function testSendIncorrectSortParams2()
    {
        $input = [
            'sort' => '-created,+,name',
        ];

        $exception = null;
        try {
            $this->parser->parse($input);
        } catch (JsonApiException $exception) {
        }

        $this->assertNotNull($exception);
        $this->assertNotEmpty($exception->getErrors());
    }

    /**
     * Test invalid params.
     *
     * Issue #58 @see https://github.com/neomerx/json-api/issues/58
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidPageParams()
    {
        $input = [
            'page' => '2',
        ];
        $this->assertNotNull($parameters = $this->parser->parse($input));
        $this->assertNull($parameters->getPaginationParameters());
    }

    /**
     * Test invalid params.
     *
     * Issue #58 @see https://github.com/neomerx/json-api/issues/58
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidFilterParams()
    {
        $input = [
            'filter' => 'whatever',
        ];
        $this->assertNotNull($parameters = $this->parser->parse($input));

        $this->assertNull($parameters->getSortParameters());
    }

    /**
     * Test invalid params.
     *
     * Issue #58 @see https://github.com/neomerx/json-api/issues/58
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidIncludeParams()
    {
        $input = [
            'include' => ['whatever'],
        ];
        $this->assertNotNull($parameters = $this->parser->parse($input));

        $this->assertNull($parameters->getIncludePaths());
    }

    /**
     * Test invalid params.
     *
     * Issue #58 @see https://github.com/neomerx/json-api/issues/58
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidSortParams()
    {
        $input = [
            'sort' => ['whatever'],
        ];
        $this->assertNotNull($parameters = $this->parser->parse($input));

        $this->assertNull($parameters->getIncludePaths());
    }

    /**
     * Test parsing unrecognized parameters.
     */
    public function testUnrecognizedParameters()
    {
        $input      = [
            'include'      => 'author,comments,comments.author',
            'unrecognized' => ['parameters'],
        ];
        $parameters = $this->parser->parse($input);

        $this->assertEquals(['unrecognized' => ['parameters']], $parameters->getUnrecognizedParameters());
    }

    /**
     * Test miss field in sort params. Sample /posts/1?fields[posts]=
     *
     * @see https://github.com/neomerx/json-api/issues/107
     */
    public function testFieldSetWithEmptyField()
    {
        $input  = [
            'fields' => ['type1' => 'fields1,fields2', 'type2' => ''],
        ];
        $result = $this->parser->parse($input);

        // note type2 has empty field set
        $this->assertEquals(['type1' => ['fields1', 'fields2'], 'type2' => []], $result->getFieldSets());
    }

    /**
     * Test miss field in sort params. Sample /posts/1?fields[posts][foo]=title
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidFieldSetWithMultiDimensionArray()
    {
        $input = [
            'fields' => ['type' => ['subtype' => 'fields1,fields2']],
        ];
        $this->parser->parse($input);
    }

    /**
     * Test that unrecognized parameters are ignored when calling `isEmpty` - issue #89
     *
     * @author https://github.com/lindyhopchris
     */
    public function testUnrecognizedParametersIgnoredByIsEmpty()
    {
        $input = [
            'unrecognized' => 'foo',
        ];

        $parameters = $this->parser->parse($input);

        $this->assertTrue($parameters->isEmpty());
        $this->assertEquals(['unrecognized' => 'foo'], $parameters->getUnrecognizedParameters());
    }
}
