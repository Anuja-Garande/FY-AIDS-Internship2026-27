<?php

session_start();

include "../includes/db_connect.php";

if(isset($_POST['issue_id'])){

    $issue_id = $_POST['issue_id'];

    // Get complete issue details
    $getIssue = mysqli_query($conn,
    "SELECT * FROM issued_books WHERE issue_id='$issue_id'");

    $issue = mysqli_fetch_assoc($getIssue);

    $book_id = $issue['book_id'];

    // Update issued book
    $updateIssue = mysqli_query($conn,

    "UPDATE issued_books

    SET status='Returned',
        return_date=CURDATE()

    WHERE issue_id='$issue_id'");

    if($updateIssue){

    // Check if already returned
    $checkReturn = mysqli_query($conn,

    "SELECT * FROM returned_books
    WHERE issue_id='{$issue['issue_id']}'");

    if(mysqli_num_rows($checkReturn) == 0){

        $insertReturn = mysqli_query($conn,

        "INSERT INTO returned_books
        (issue_id, student_id, book_id, return_date, fine_paid, remarks)

        VALUES
        (
        '{$issue['issue_id']}',
        '{$issue['student_id']}',
        '{$issue['book_id']}',
        CURDATE(),
        '{$issue['fine']}',
        'Returned Successfully'
        )");

        if(!$insertReturn){
            die(mysqli_error($conn));
        }
    }
        // Increase available quantity
        mysqli_query($conn,

        "UPDATE books

        SET available_quantity = available_quantity + 1

        WHERE book_id='$book_id'");

        header("Location: borrowed_books.php");
        exit();

    }
    else{

        echo "Return Failed: ".mysqli_error($conn);

    }

}
else{

    echo "Invalid Request";

}

?>