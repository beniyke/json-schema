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

use Opis\JsonSchema\{
    Helper,
    IContext,
    IKeyword,
    ISchema
};
use Opis\JsonSchema\Errors\IValidationError;

class TypeKeyword implements IKeyword
{
    use ErrorTrait;

    /** @var string|string[] */
    protected $type;

    /**
     * @param string|string[] $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function validate(IContext $context, ISchema $schema): ?IValidationError
    {
        $type = $context->currentDataType();
        if ($type && Helper::jsonTypeMatches($type, $this->type)) {
            return null;
        }

        return $this->error($schema, $context, 'type', 'The data must match the type', [
            'expected' => $this->type,
            'type' => $type,
        ]);
    }
}