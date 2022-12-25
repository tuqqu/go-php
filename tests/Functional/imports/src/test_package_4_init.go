package test_package_4_init

func init() {
    println("test_package_4_init init called")
    A = 10
    B = A + A
}

var A int
var B int
