# Sample SQL Report for SQLite

This document outlines the steps to create a SQLite database using the SQL commands found in `./reports/sample-1.sql`.

## Prerequisites

- PHP 8.1 installed on your system.
- SQLite3 installed on your system (retrievable from: sqlite.org/download.html).

## Step-by-Step Guide

1. Use the terminal to navigate to the directory containing your .sql file.

    ```bash
    cd reports
    ```

2. Initialize a new SQLite database.

    ```bash
    sqlite3 sample-1.db < sample-1.sql
    ```

Congratulations! You have created your SQLite database based on `./reports/sample-1.sql`. You can now interact with the database as per your needs.

## Interacting with the Database

To interact with your SQLite database, use SQLite commands within your SQLite session. For example:
