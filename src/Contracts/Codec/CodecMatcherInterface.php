<?php namespace Neomerx\JsonApi\Contracts\Codec;

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

use Closure;
use Neomerx\JsonApi\Contracts\Decoder\DecoderInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\AcceptHeaderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\AcceptMediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;

/**
 * @package Neomerx\JsonApi
 */
interface CodecMatcherInterface
{
    /**
     * Register encoder.
     *
     * @param MediaTypeInterface $mediaType
     * @param Closure            $encoderClosure
     *
     * @return void
     */
    public function registerEncoder(MediaTypeInterface $mediaType, Closure $encoderClosure): void;

    /**
     * Register decoder.
     *
     * @param MediaTypeInterface $mediaType
     * @param Closure            $decoderClosure
     *
     * @return void
     */
    public function registerDecoder(MediaTypeInterface $mediaType, Closure $decoderClosure): void;

    /**
     * Get encoder.
     *
     * @return EncoderInterface|null
     */
    public function getEncoder(): ?EncoderInterface;

    /**
     * Set encoder.
     *
     * @param EncoderInterface|Closure $encoder
     *
     * @return void
     */
    public function setEncoder($encoder): void;

    /**
     * Get decoder.
     *
     * @return DecoderInterface|null
     */
    public function getDecoder(): ?DecoderInterface;

    /**
     * Set decoder.
     *
     * @param DecoderInterface|Closure $decoder
     *
     * @return void
     */
    public function setDecoder($decoder): void;

    /**
     * Find best encoder match for 'Accept' header.
     *
     * @param AcceptHeaderInterface $acceptHeader
     *
     * @return void
     */
    public function matchEncoder(AcceptHeaderInterface $acceptHeader): void;

    /**
     * Find best decoder match for 'Content-Type' header.
     *
     * @param HeaderInterface $contentTypeHeader
     *
     * @return void
     */
    public function matchDecoder(HeaderInterface $contentTypeHeader): void;

    /**
     * Get media type from 'Accept' header that matched to one of the registered encoder media types.
     *
     * @return AcceptMediaTypeInterface|null
     */
    public function getEncoderHeaderMatchedType(): ?AcceptMediaTypeInterface;

    /**
     * Get media type that was registered for matched encoder.
     *
     * @return MediaTypeInterface|null
     */
    public function getEncoderRegisteredMatchedType(): ?MediaTypeInterface;

    /**
     * Get media type from 'Content-Type' header that matched to one of the registered decoder media types.
     *
     * @return MediaTypeInterface|null
     */
    public function getDecoderHeaderMatchedType(): ?MediaTypeInterface;

    /**
     * Get media type that was registered for matched decoder.
     *
     * @return MediaTypeInterface|null
     */
    public function getDecoderRegisteredMatchedType(): ?MediaTypeInterface;
}
