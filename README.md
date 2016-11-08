# rideshare
Rideshare Project: The Easier Way to Find a Ride to SLO
This project aims to train a classifier to label posts on the [Cal Poly Ridesharing Facebook group] (https://www.facebook.com/groups/250502971675365/) as either 'offering' or 'seeking'.

# PHP
The php directory holds the code for obtaining the posts using the [Facebook PHP SDK] (../../facebook/php-graph-sdk), preprocessing them, and then storing them

# Python
The Python directory hold the code for training, testing, and classifiying the posts. We are using the naive bayes algorithm from the NLTK library and k nearest neighbor from sklearn library. We are using Python 3.
