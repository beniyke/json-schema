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

namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{IContext, IKeyword, ISchema};
use Opis\JsonSchema\Errors\IValidationError;

class ContentSchemaKeyword implements IKeyword
{
    use ErrorTrait;
    use DecodedContentTrait;

    /** @var bool|object */
    protected $value;

    /**
     * @param object $value
     */
    public function __construct(object $value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function validate(IContext $context, ISchema $schema): ?IValidationError
    {
        $data = json_decode($this->getDecodedContent($context), false);

        if ($error = json_last_error() !== JSON_ERROR_NONE) {
            $message = json_last_error_msg();

            return $this->error($schema, $context, 'contentSchema', "Invalid JSON content: {$message}", [
                'error' => $error,
                'message' => $message,
            ]);
        }

        if (is_object($this->value) && !($this->value instanceof ISchema)) {
            $this->value = $context->loader()->loadObjectSchema($this->value);
        }

        if ($error = $this->value->validate($context->newInstance($data))) {
            return $this->error($schema, $context, 'contentSchema', "The JSON content must match schema", [], $error);
        }

        return null;
    }
}