package test_package_1

const X string = "x_from_test_package_1"

var C int = 0

func TestPrint() {
    println(test_package_1.X)
}

func TestPrintCounter() {
    println(C)
}
