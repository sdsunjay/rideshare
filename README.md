# rideshare
This project aims to train a classifier to label posts on the Cal Poly Ridesharing Facebook group as either 'offering' or 'seeking'. The current accuracy \(as of [5450d05](commit/5450d058b4add0ba3d963f34b200b836bf2823b0)\) is around 98%. 

## Motivation
I created this project to quickly and easily find rides. The Facebook page has many posts and it can take a lot of time to scroll through all of them. This project will eventually allow the user to filter based on offering/seeking, location, and date/time leaving.

## Code

### PHP
The php directory holds the code for obtaining the posts using the [Facebook PHP SDK] (../../facebook/php-graph-sdk), preprocessing them, and then storing them in a file or database. We are using PHP 5.6.
#### Running
If you want to run this program, then you need to make sure to create a config.php file in the same directory. This file should define the following constants:
  - USERNAME
  - PASSWORD
  - HOST
  - APP_ID
  - APP_SECRET
  

### Python
The Python directory hold the code for training, testing, and classifiying the posts. We are using the naive bayes algorithm from the NLTK library and k nearest neighbor from sklearn library. We are using Python 3.
#### Running
``` shell
$ python classifier.py
```


