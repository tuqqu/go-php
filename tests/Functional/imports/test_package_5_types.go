package test_package_5_types

type TestInt int

type TestBoolMap map[bool]bool

type TestIntMap map[int][]TestInt

type TestStringAlias = string

type TestIntAliasOfCustom = TestInt

type Point struct {
	x TestInt
	y TestInt
}
