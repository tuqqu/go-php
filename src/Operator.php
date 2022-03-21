<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\Operator as AstOperator;

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
    case LeftShift = '<<';
    case RightShift = '>>';

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
    case LeftShiftEq = '<<=';
    case RightShiftEq = '>>=';

    // logic operators
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

    public static function fromAst(AstOperator $op): self
    {
        return self::tryFrom($op->value) ?? throw new \Exception('unknown op');
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
            self::LeftShiftEq,
            self::RightShiftEq => true,
            default => false,
        };
    }

    public function disjoin(): self
    {
        return match ($this) {
            self::PlusEq => self::Plus,
            self::MinusEq => self::Minus,
            self::MulEq => self::Mul,
            self::DivEq => self::Div,
            self::ModEq => self::Mod,
            self::BitAndEq => self::BitAnd,
            self::BitOrEq => self::BitOr,
            self::BitXorEq => self::BitXor,
            self::BitAndNotEq => self::BitAndNot,
            self::LeftShiftEq => self::LeftShift,
            self::RightShiftEq => self::RightShift,
            default => throw new \Exception('not compund'),
        };
    }
}
