package test_package_3_init

func init() {
    println("test_package_3_init init1 called")
    A = 10
    B = A + A
}

func init() {
    println("test_package_3_init init2 called")
    A++
    C = B + A
}

var A int
var B int
var C int
