package main

import "countsort"

func main() {
	var array = []int{12, 2, 35, 4, 59, 6, 7, -8, -99, 0, 10}

	println("Unsorted array:")
	printArray(array)

	countsort.CountSort(array)

	println("Sorted array:")
	printArray(array)
}

func printArray(array []int) {
	for _, value := range array {
		print(value, " ")
	}

	println()
}
