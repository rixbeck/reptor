# Reptor
## PhpSpreadsheet based report generator


The PHPSpreadsheet Report Generator is a PHP-orientated solution curated to empower users to generate reports seamlessly. Embedded with the prevailing attributes of the PHP 8.1 enabled PHPSpreadsheet library, this project ensures the generation of dynamic XLSX reports using Excel-based template files.

The project aims to revolutionize report generation in the _PHP world_ by converting raw data into comprehensive, digestible information punctuated with the expansive expressive ability of the Symfony Expression Language. Facilitating the inclusion of data sources or datasets makes it a versatile tool for diverse reporting needs.

Whether you are an individual aiming for personal data understanding or an enterprise seeking to streamline complex data analysis, the PHPSpreadsheet Report Generator serves to be an invaluable tool to garner insights, make strategic decisions or simply comprehend an array of information.

The project was highly inspired by [alhimik1986](https://github.com/alhimik1986) and his [PHP Excel Templator](https://github.com/alhimik1986/php-excel-templator) library.

## Key Features:

1. **Enhanced Report Generation**: Create reports efficiently in XLSX, PDF, HTML and other formats.
2. **Symfony Expression Language**: Harness the power of Symfony’s component to provide a simplified manipulation of your object graph.
3. **Data Sources or Datasets**: Include a multitude of data end points or vast datasets to generate reports that match your preferences and requirements.
4. **Template Language Extensibility**: The ability to extend the template language allows for more personalized and complex templates, accommodating a great degree of customization and functionality.
5. **Aggregator Functions**: The ability to use aggregator functions like as `sum`, `avg`, etc. the generation of reports with a greater degree of complexity and functionality.
6. **Group By**: The ability to group data by a specific column allows for the generation of reports that are more organized and structured.


![img-1.png](docs%2Fimg-1.png)

## Installation

Reptor can be acquired and installed through a Git clone, followed by a Composer installation. Please follow these steps:

1. If you haven't installed Git or Composer, start by downloading and installing them. Use the official download pages for [Git](https://git-scm.com/downloads) and [Composer](https://getcomposer.org/download/).

2. Once Git and Composer are installed, clone Reptor repository into your local environment:

    ```bash
    git clone https://github.com/rixbeck/Reptor.git
    ```
   
3. Navigate to the cloned repository and install the dependencies:

    ```bash
    cd Reptor
    composer install
    ```

Make sure your PHP environment meets the requirements specified in the composer.json file, including enabled extensions like PDO, JSON, and SQLite3.
After completing these steps, the PHPSpreadsheet Report Generator is ready for use in your project.

## Usage

