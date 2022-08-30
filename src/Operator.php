<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\Operator as AstOperator;
use GoPhp\Error\InternalError;

enum Operator: string
{
    // binary
    case Plus = '+';
    case Minus = '-';
    case Mul = '*';
    case Div = '/';
    case Mod = '%';

    case BitAnd = '&';
    case BitOr = '|';
    case BitXor = '^';
    case BitAndNot = '&^';
    case ShiftLeft = '<<';
    case ShiftRight = '>>';

    // logic
    case LogicAnd = '&&';
    case LogicOr = '||';
    case LogicNot = '!';

    // equality & comparison
    case EqEq = '==';
    case NotEq = '!=';
    case Less = '<';
    case LessEq = '<=';
    case Greater = '>';
    case GreaterEq = '>=';

    // assignment

    case Eq = '=';

    case PlusEq = '+=';
    case MinusEq = '-=';
    case MulEq = '*=';
    case DivEq = '/=';
    case ModEq = '%=';

    case BitAndEq = '&=';
    case BitOrEq = '|=';
    case BitXorEq = '^=';
    case BitAndNotEq = '&^=';
    case ShiftLeftEq = '<<=';
    case ShiftRightEq = '>>=';

    case Inc = '++';
    case Dec = '--';

    public static function fromAst(AstOperator $op): self
    {
        return self::tryFrom($op->value) ?? throw InternalError::unknownOperator($op->value);
    }

    public function isAssignment(): bool
    {
        return match ($this) {
            self::Eq => true,
            default => $this->isCompound(),
        };
    }

    public function isCompound(): bool
    {
        return match ($this) {
            self::PlusEq,
            self::MinusEq,
            self::MulEq,
            self::DivEq,
            self::ModEq,
            self::BitAndEq,
            self::BitOrEq,
            self::BitXorEq,
            self::BitAndNotEq,
            self::ShiftLeftEq,
            self::ShiftRightEq,
            self::Inc,
            self::Dec => true,
            default => false,
        };
    }
}
