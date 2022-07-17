package main

import (
    "test_package_3_init"
    "test_package_4_init"
)

func main() {
    println(test_package_3_init.A)
    println(test_package_3_init.B)
    println(test_package_3_init.C)

    println(test_package_4_init.A)
    println(test_package_4_init.B)
}

func init() {
    println("main init called")
    A = 1
}

var A int
