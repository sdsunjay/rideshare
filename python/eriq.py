# from textblob.classifiers import NaiveBayesClassifier
# from textblob import TextBlob

import random
import re

import nltk

allWords = []

def cleanText(text):
   return re.sub(r'\s+', ' ', re.sub(r'\W+', " ", text.lower())).strip()

# |allText| is a list of strings.
# Find all unique words in |allText|.
def getAllWords(allText):
   global allWords

   words = {}
   for text in allText:
      for word in text.split():
	 words[word] = True
   allWords = words.keys()

# Given some text (|text|), return a dict of features for that text.
def getFeatures(text):
   global allWords

   features = {}
   # Turn the input into a set of words.
   textSet = set(text.split())
   for word in allWords:
      features["Has: %s" % word] = (word in textSet)
   return features 

def evaluate(allData):
   folds = 10
   # TODO(Someone): Precision Math
   instancesPerFold = (int)(len(allData) / folds)
   
   accuracy = 0
   for i in range(0, folds):
      test = allData[(i + 0) * instancesPerFold:(i + 1) * instancesPerFold]
      train = allData[0:(i + 0) * instancesPerFold] + allData[(i + 1) * instancesPerFold:]
      
      runAccuracy = run(test, train)
      print("Run %d: %0.5f" % (i, runAccuracy))

      accuracy += runAccuracy

   print("Total Accuracy: %0.5f" % (accuracy / folds))

# Each argument is a list of strings
def run(test, train):
   # Convert list of strings into list of features (dict of features).
   trainingSet = nltk.classify.apply_features(getFeatures, train)
   testSet = nltk.classify.apply_features(getFeatures, test)

   classy = nltk.NaiveBayesClassifier.train(trainingSet)
   return nltk.classify.accuracy(classy, testSet)

allData = []

with open('training_texts/seeking.txt', 'r') as f:
   for line in f:
      allData.append((cleanText(line), 'seeking'))   

with open('training_texts/offering.txt', 'r') as f:
   for line in f:
      allData.append((cleanText(line), 'offering')) 

# Everyday I'm shuffling.
random.shuffle(allData)

getAllWords([instance[0] for instance in allData])
evaluate(allData)
