# from textblob.classifiers import NaiveBayesClassifier
# from textblob import TextBlob
from textblob.classifiers import NaiveBayesClassifier
from textblob import TextBlob

import copy

from nltk import PorterStemmer
# from nltk.stem.porter import *
from nltk.stem.snowball import SnowballStemmer
import collections
import nltk.classify.util, nltk.metrics
#from nltk.classify import NaiveBayesClassifier
from nltk.corpus import stopwords
import random
import re

allWords = []


def cleanText(text):
    """Clean up the text."""
    return re.sub(r'\s+', ' ', re.sub(r'\W+', " ", text.lower())).strip()


def getAllWords(allText):
    """Find all unique words in the text (|allText|). |allText| is a list of strings."""
    global allWords

    # stemmer = SnowballStemmer("english")
    words = {}
    for text in allText:
        for word in text.split():
           words[word] = True
    allWords = words.keys()
    allWords = stopword_filtered_word_feats(allWords)

def getFeatureSets(text):
    """Given a list of strings (|text|), return a set of words for that text."""
    global allWords
    features = {}
    textSet = set()
    # Turn the input into a set of words.
    for line in text:
       new_line = line[0].split()
       for word in new_line:
          textSet.add(word)
    return textSet

def getFeatures(text):
    """Given some text (|text|), return a dict of features for that text."""
    global allWords

    features = {}
    # Turn the input into a set of words.
    textSet = set(text.split())
    for word in allWords:
        features["Has: %s" % word] = (word in textSet)
    return features


def splitIntoFolds(lst, n):
    """Split lst into n chunks."""
    division = len(lst) / float(n)
    #return [lst[int(round(division * i)): int(round(division * (i + 1)))] for i in xrange(n)]
    return [lst[int(round(division * i)): int(round(division * (i + 1)))] for i in range(n)]


def evaluate(allData, numberOfFolds):
    """For cross-validation, we divide the data (|allData|) into N (|numberOfFolds|) subsets. Then we run each subset."""
    accuracy = 0
    folds = splitIntoFolds(allData, numberOfFolds)
    startIndex = 0
    for i in range(0, numberOfFolds):
        endIndex = startIndex + len(folds[i]) - 1
        training = folds[0:(i + 0)] + folds[(i + 1):]
        mergedTraining = [item for sublist in training for item in sublist]
        test = folds[i]

        runAccuracy = run(test, mergedTraining)
        #print("Run %d (%d - %d) : %0.5f Accuracy" %
        #      (i, startIndex, endIndex, runAccuracy))
        #startIndex = len(folds[i]) + startIndex

       # accuracy += runAccuracy

    #print("Total Accuracy: %0.5f" % (accuracy / numberOfFolds))
 
def stopword_filtered_word_feats(allWords):
    stopset = set(stopwords.words('english'))
    return dict([(word, True) for word in allWords if word not in stopset])

#    train = [('desperately seeking slo glendale area friday may 22 4pm seeking 2 spots.', 'pos'), ('i have a couple of announcements i am offering a trip to oxnard and back on thursday also i will be in the bay all summer but i am coming back to slo every friday night and going back to redwood city every sunday night connect with me if you need a ride ever.', 'pos'), ('desperately seeking slo to berkeley bart general area friday may 22 before noon will provide gas money.', 'pos'), ('desparately seeking slo to sj now', 'pos'), ('kinda backwards but maybe someone has friends so in san diego offering san diego to slo thursday afternoon early friday morning slo to san diego sunday afternoon', 'pos'), ('kinda random but offering slo to east bay livermore tues 19th at around 2 and back early thursday morning', 'pos'), ('im not sure how many people are going to need a ride but offering slo san jose thursday after 4', 'neg'), ('help my ride cancelled on me seeking bay area to slo anytime sunday i can meet at pretty much any bart station', 'neg'), ('do any of your friends want to visit for memorial day weekend offering sd to slo may 22 at 11 12pm slo to sd may 23 at 12pm can pick up along the way', 'pos'), ('before i book a bus ticket seeking slo to santa clara san jose area thursday 5 28 before 2 will help pay for gas', 'pos')]
def smetrics(test, train):    
    cl = NaiveBayesClassifier(train)

    # Classify some text
    print(cl.classify("Their burgers are amazing."))  # "pos"
    print(cl.classify("I don't like their pizza."))   # "neg"


    #print('Training: ')
    #print(train)
    #print('\n')    
    #print('Testing: ')
    #print(test)
    #print('\n')
    #cl = NaiveBayesClassifier(train)

   # Classify some text
    #print(cl.classify("Their burgers are amazing."))  # "pos"
    #print(cl.classify("I don't like their pizza."))   # "neg"
    #errors = []
    #print(classy.classify("We like burgers."))
   #for (line, tag) in testSet:
   # #    guess = classy.classify(line)
    #    if guess != tag:
     #       errors.append( (tag, guess, line) )
      #      for (tag, guess, line) in sorted(errors):
      #          print('correct={:<8} guess={:<8s} name={:<30}'.format(tag, guess, name))
    # print 'accuracy:', nltk.classify.util.accuracy(classifier, tesSet)
    # return nltk.classify.accuracy(classy, testSet)
    # print 'accuracy:', nltk.classify.util.accuracy(classy, testSet)
    # print 'precision score: ', nltk.metrics.scores.precision(trainingSet,testSet)
    # print 'pos precision:', nltk.classify.util.precision(classy, testSet)
    # print 'pos precision:', nltk.metrics.precision(trainingSet, testSet)
    # print 'pos recall:', nltk.metrics.recall(trainingSet, testSet)
    # print 'neg precision:', nltk.metrics.precision(trainingSet, testSet)
    # print 'neg recall:', nltk.metrics.recall(trainingSet, testSet)

def run(test, train):
    """Convert list of tuples (label, words) into list of features (dict of features). Each argument is a list of strings."""
     # print test
    # testFeatures = getFeatures(test)
    # trainFeatures = getFeatures(train)
    #trainingSet = nltk.classify.apply_features(getFeatures, train)
    #testSet = nltk.classify.apply_features(getFeatures, test)

    #classy = NaiveBayesClassifier.train(trainingSet)
    #refSets = getFeatureSets(train)
    #testSets = getFeatureSets(test)
    
# collections.defaultdict(set)
    # testSets = collections.defaultdict(set)
    #for i, (feats, label) in enumerate(test):
    #        refSets[label].add(i)
     #       observed = classy.classify(feats)
     #       testSets[observed].add(i)
#       nltk.classify.apply_features
    smetrics(test, train)
#    classy.show_most_informative_features(10)
#    return nltk.classify.accuracy(classy, testSet)


def main():
    """The main function."""
    numberOfFolds = 2
    allData = []

    with open('training_texts/small_seeking.txt', 'r') as f:
        for line in f:
            allData.append((cleanText(line), 'seeking'))

    with open('training_texts/small_offering.txt', 'r') as f:
        for line in f:
            allData.append((cleanText(line), 'offering'))

    # Every day I'm shuffling.
    random.shuffle(allData)

    print("Total number of lines in training set: %d" % len(allData))
    getAllWords([instance[0] for instance in allData])
    evaluate(allData, numberOfFolds)

if __name__ == "__main__":
    main()
