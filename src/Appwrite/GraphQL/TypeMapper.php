<?php

namespace Appwrite\GraphQL;

use Appwrite\Utopia\Response\Model\Attribute;
use Exception;
use GraphQL\Type\Definition\Type;
use Utopia\App;
use Utopia\Route;
use Utopia\Validator;

class TypeMapper
{
    /**
     * Map a {@see Route} parameter to a GraphQL Type
     *
     * @param App $utopia
     * @param Validator|callable $validator
     * @param bool $required
     * @param array $injections
     * @return Type
     * @throws Exception
     */
    public static function fromRouteParameter(
        App $utopia,
        Validator|callable $validator,
        bool $required,
        array $injections
    ): Type {
        $validator = \is_callable($validator)
            ? \call_user_func_array($validator, $utopia->getResources($injections))
            : $validator;

        switch ((!empty($validator)) ? \get_class($validator) : '') {
            case 'Appwrite\Auth\Validator\Password':
            case 'Appwrite\Event\Validator\Event':
            case 'Appwrite\Network\Validator\CNAME':
            case 'Appwrite\Network\Validator\Domain':
            case 'Appwrite\Network\Validator\Email':
            case 'Appwrite\Network\Validator\Host':
            case 'Appwrite\Network\Validator\IP':
            case 'Appwrite\Network\Validator\Origin':
            case 'Appwrite\Network\Validator\URL':
            case 'Appwrite\Task\Validator\Cron':
            case 'Appwrite\Utopia\Database\Validator\CustomId':
            case 'Utopia\Database\Validator\Key':
            case 'Utopia\Database\Validator\CustomId':
            case 'Utopia\Database\Validator\UID':
            case 'Utopia\Validator\HexColor':
            case 'Utopia\Validator\Length':
            case 'Utopia\Validator\Text':
            case 'Utopia\Validator\WhiteList':
            default:
                $type = Type::string();
                break;
            case 'Utopia\Validator\Boolean':
                $type = Type::boolean();
                break;
            case 'Utopia\Validator\ArrayList':
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                $type = Type::listOf(self::fromRouteParameter(
                    $utopia,
                    $validator->getValidator(),
                    $required,
                    $injections
                ));
                break;
            case 'Utopia\Validator\Numeric':
            case 'Utopia\Validator\Integer':
            case 'Utopia\Validator\Range':
                $type = Type::int();
                break;
            case 'Utopia\Validator\FloatValidator':
                $type = Type::float();
                break;
            case 'Utopia\Database\Validator\Authorization':
            case 'Utopia\Database\Validator\Permissions':
            case 'Utopia\Database\Validator\Roles':
            case 'Appwrite\Utopia\Database\Validator\Queries':
            case 'Appwrite\Utopia\Database\Validator\Queries\Base':
            case 'Appwrite\Utopia\Database\Validator\Queries\Buckets':
            case 'Appwrite\Utopia\Database\Validator\Queries\Collections':
            case 'Appwrite\Utopia\Database\Validator\Queries\Databases':
            case 'Appwrite\Utopia\Database\Validator\Queries\Deployments':
            case 'Appwrite\Utopia\Database\Validator\Queries\Documents':
            case 'Appwrite\Utopia\Database\Validator\Queries\Executions':
            case 'Appwrite\Utopia\Database\Validator\Queries\Files':
            case 'Appwrite\Utopia\Database\Validator\Queries\Functions':
            case 'Appwrite\Utopia\Database\Validator\Queries\Memberships':
            case 'Appwrite\Utopia\Database\Validator\Queries\Projects':
            case 'Appwrite\Utopia\Database\Validator\Queries\Teams':
            case 'Appwrite\Utopia\Database\Validator\Queries\Users':
            case 'Appwrite\Utopia\Database\Validator\Queries\Variables':
                $type = Type::listOf(Type::string());
                break;
            case 'Utopia\Validator\Assoc':
            case 'Utopia\Validator\JSON':
                $type = TypeRegistry::json();
                break;
            case 'Utopia\Storage\Validator\File':
                $type = TypeRegistry::inputFile();
                break;
        }

        if ($required) {
            $type = Type::nonNull($type);
        }

        return $type;
    }

    /**
     * Map an {@see Attribute} to a GraphQL Type
     *
     * @param string $type
     * @param bool $array
     * @param bool $required
     * @return Type
     * @throws Exception
     */
    public static function fromCollectionAttribute(string $type, bool $array, bool $required): Type
    {
        if ($array) {
            return Type::listOf(self::fromCollectionAttribute($type, false, $required));
        }

        $type = match ($type) {
            'boolean' => Type::boolean(),
            'integer' => Type::int(),
            'double' => Type::float(),
            default => Type::string(),
        };

        if ($required) {
            $type = Type::nonNull($type);
        }

        return $type;
    }
}