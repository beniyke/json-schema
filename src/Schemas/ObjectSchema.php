<?php
/* ============================================================================
 * Copyright 2020 Zindex Software
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\JsonSchema\Schemas;

use Opis\JsonSchema\{Helper, Keyword, ValidationContext, WrapperKeyword};
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\WrapperKeywords\CallbackWrapperKeyword;

class ObjectSchema extends AbstractSchema
{

    protected ?WrapperKeyword $wrapper = null;

    /** @var Keyword[]|null */
    protected ?array $before = null;

    /** @var Keyword[]|null */
    protected ?array $after = null;

    /** @var Keyword[][]|null */
    protected ?array $types = null;

    /**
     * @param SchemaInfo $info
     * @param WrapperKeyword|null $wrapper
     * @param Keyword[][]|null $types
     * @param Keyword[]|null $before
     * @param Keyword[]|null $after
     */
    public function __construct(SchemaInfo $info, ?WrapperKeyword $wrapper, ?array $types, ?array $before, ?array $after)
    {
        parent::__construct($info);
        $this->types = $types;
        $this->before = $before;
        $this->after = $after;
        $this->wrapper = $wrapper;

        if ($wrapper) {
            while ($next = $wrapper->next()) {
                $wrapper = $next;
            }
            $wrapper->setNext(new CallbackWrapperKeyword([$this, 'doValidate']));
        }
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context): ?ValidationError
    {
        $context->pushSharedObject();
        $error = $this->wrapper ? $this->wrapper->validate($context) : $this->doValidate($context);
        $context->popSharedObject();

        return $error;
    }

    /**
     * @param ValidationContext $context
     * @return null|ValidationError
     *@internal
     */
    public function doValidate(ValidationContext $context): ?ValidationError
    {
        if ($this->before && ($error = $this->applyKeywords($this->before, $context))) {
            return $error;
        }

        if ($this->types && ($type = $context->currentDataType())) {
            if (isset($this->types[$type]) && ($error = $this->applyKeywords($this->types[$type], $context))) {
                return $error;
            }

            if (($type = Helper::getJsonSuperType($type)) && isset($this->types[$type])) {
                if ($error = $this->applyKeywords($this->types[$type], $context)) {
                    return $error;
                }
            }

            unset($type);
        }

        if ($this->after && ($error = $this->applyKeywords($this->after, $context))) {
            return $error;
        }

        return null;
    }

    /**
     * @param Keyword[] $keywords
     * @param ValidationContext $context
     * @return ValidationError|null
     */
    protected function applyKeywords(array $keywords, ValidationContext $context): ?ValidationError
    {
        foreach ($keywords as $keyword) {
            if ($error = $keyword->validate($context, $this)) {
                return $error;
            }
        }

        return null;
    }
}