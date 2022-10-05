
### Description

Script that converts database data to an XML file.

___

### Requirements to run script

- PHP >= 7.4
- MySQL >= 5.7.39
- composer 2.3.10

___

### Step by step

1. To add a database to the project run ***webdev_test.sql*** file

2. Install the needed dependencies using ***composer***

3. Rename the ***.env.example*** file to ***.env*** and  change necessary <strong>values</strong> to the appropriate values for ***your database***:

- 'localhost'
- 'user'
- 'password'
- 'database'

6. run ***webdev.php*** file to get saved XML file products.xml with selected data.
