<?php namespace Neomerx\JsonApi\Codec;

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
use Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use Neomerx\JsonApi\Contracts\Decoder\DecoderInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\AcceptHeaderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\AcceptMediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;

/**
 * @package Neomerx\JsonApi
 */
class CodecMatcher implements CodecMatcherInterface
{
    /**
     * @var array Supported JSON API media types with extensions and their combinations for responses.
     *
     * Servers may support multiple media types at any endpoint. For example, a server may choose to
     * support text/html in order to simplify viewing content via a web browser.
     *
     * JSON API specifications says that input requests might ask output in combination of formats (e.g. "ext1,ext2"
     * which means it should be formatted according to the extensions "ext1" and "ext2".
     *
     * Note: Since extensions can contradict one another or have interactions that can be resolved in many
     * equally plausible ways, it is the responsibility of the server to decide which extensions are compatible,
     * and it is the responsibility of the designer of each implementation of this specification to describe
     * extension interoperability rules which are applicable to that implementation.
     */
    private $outputMediaTypes;

    /**
     * @var array Supported JSON API extensions and their combinations for requests.
     * Similar to supported media types for responses.
     */
    private $inputMediaTypes;

    /**
     * @var EncoderInterface|Closure|null
     */
    private $foundEncoder;

    /**
     * @var DecoderInterface|Closure|null
     */
    private $foundDecoder;

    /**
     * @var AcceptMediaTypeInterface|null
     */
    private $encoderHeaderMatchedType;

    /**
     * @var MediaTypeInterface|null
     */
    private $encoderRegisteredMatchedType;

    /**
     * @var MediaTypeInterface|null
     */
    private $decoderHeaderMatchedType;

    /**
     * @var MediaTypeInterface|null
     */
    private $decoderRegisteredMatchedType;

    /**
     * @inheritdoc
     */
    public function registerEncoder(MediaTypeInterface $mediaType, Closure $encoderClosure): void
    {
        $this->outputMediaTypes[] = [$mediaType, $encoderClosure];
    }

    /**
     * @inheritdoc
     */
    public function registerDecoder(MediaTypeInterface $mediaType, Closure $decoderClosure): void
    {
        $this->inputMediaTypes[] = [$mediaType, $decoderClosure];
    }

    /**
     * @inheritdoc
     */
    public function getEncoder(): ?EncoderInterface
    {
        if ($this->foundEncoder instanceof Closure) {
            $closure = $this->foundEncoder;
            $encoder = $closure();

            assert($encoder instanceof EncoderInterface);

            $this->setEncoder($encoder);
        }

        return $this->foundEncoder;
    }

    /**
     * @inheritdoc
     */
    public function setEncoder($encoder): void
    {
        assert($encoder instanceof EncoderInterface || $encoder instanceof Closure);

        $this->foundEncoder = $encoder;
    }

    /**
     * @inheritdoc
     */
    public function getDecoder(): ?DecoderInterface
    {
        if ($this->foundDecoder instanceof Closure) {
            $closure = $this->foundDecoder;
            $decoder = $closure();

            assert($decoder instanceof DecoderInterface);

            $this->setDecoder($decoder);
        }

        return $this->foundDecoder;
    }

    /**
     * @inheritdoc
     */
    public function setDecoder($decoder): void
    {
        assert($decoder instanceof DecoderInterface || $decoder instanceof Closure);

        $this->foundDecoder = $decoder;
    }

    /**
     * @inheritdoc
     */
    public function matchEncoder(AcceptHeaderInterface $acceptHeader): void
    {
        foreach ($acceptHeader->getMediaTypes() as $headerMediaType) {
            // if quality factor 'q' === 0 it means this type is not acceptable (RFC 2616 #3.9)
            if ($headerMediaType->getQuality() > 0) {
                /** @var MediaTypeInterface $registeredType */
                foreach ($this->outputMediaTypes as list($registeredType, $closure)) {
                    assert($closure instanceof Closure);
                    if ($registeredType->matchesTo($headerMediaType) === true) {
                        $this->setEncoderHeaderMatchedType($headerMediaType);
                        $this->setEncoderRegisteredMatchedType($registeredType);
                        $this->setEncoder($closure);

                        return;
                    }
                }
            }
        }

        $this->setEncoderHeaderMatchedType(null);
        $this->setEncoderRegisteredMatchedType(null);
        $this->foundEncoder = null;
    }

    /**
     * Find decoder with media type equal to media type in 'Content-Type' header.
     *
     * @param HeaderInterface $contentTypeHeader
     *
     * @return void
     */
    public function matchDecoder(HeaderInterface $contentTypeHeader): void
    {
        foreach ($contentTypeHeader->getMediaTypes() as $headerMediaType) {
            /** @var MediaTypeInterface $registeredType */
            foreach ($this->inputMediaTypes as list($registeredType, $closure)) {
                if ($registeredType->equalsTo($headerMediaType) === true) {
                    $this->decoderHeaderMatchedType     = $headerMediaType;
                    $this->decoderRegisteredMatchedType = $registeredType;
                    $this->setDecoder($closure);

                    return;
                }
            }
        }

        $this->decoderHeaderMatchedType     = null;
        $this->decoderRegisteredMatchedType = null;
        $this->foundDecoder                 = null;
    }

    /**
     * @inheritdoc
     */
    public function getEncoderHeaderMatchedType(): ?AcceptMediaTypeInterface
    {
        return $this->encoderHeaderMatchedType;
    }

    /**
     * @inheritdoc
     */
    public function getEncoderRegisteredMatchedType(): ?MediaTypeInterface
    {
        return $this->encoderRegisteredMatchedType;
    }

    /**
     * @inheritdoc
     */
    public function getDecoderHeaderMatchedType(): ?MediaTypeInterface
    {
        return $this->decoderHeaderMatchedType;
    }

    /**
     * @inheritdoc
     */
    public function getDecoderRegisteredMatchedType(): ?MediaTypeInterface
    {
        return $this->decoderRegisteredMatchedType;
    }

    /**
     * @param AcceptMediaTypeInterface|null $encoderHeaderMatchedType
     *
     * @return self
     */
    private function setEncoderHeaderMatchedType(?AcceptMediaTypeInterface $encoderHeaderMatchedType): self
    {
        $this->encoderHeaderMatchedType = $encoderHeaderMatchedType;

        return $this;
    }

    /**
     * @param MediaTypeInterface|null $encoderRegisteredMatchedType
     *
     * @return self
     */
    private function setEncoderRegisteredMatchedType(?MediaTypeInterface $encoderRegisteredMatchedType): self
    {
        $this->encoderRegisteredMatchedType = $encoderRegisteredMatchedType;

        return $this;
    }
}
