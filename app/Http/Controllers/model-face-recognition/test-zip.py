# from zipfile import ZipFile
# from io import BytesIO
# from PIL import Image
# import matplotlib.pyplot as plt
import sys 
import cv2
import numpy as np
from keras_facenet import FaceNet
import joblib
import os
import glob

file_name = sys.argv[1]

# dataset
root_dir = file_name

# get list of image file paths and labels
image_paths = []
labels = []
for subdir, dirs, files in os.walk(root_dir):
    label = os.path.basename(subdir)
    label = label.split('-')
    label = label[0]
    for file in files:
        if file.endswith('.jpg') or file.endswith('.jpeg') or file.endswith('.png'):
            image_paths.append(os.path.join(subdir, file))
            labels.append(label)

# print(data_master)

# Load Haar Cascade untuk deteksi wajah
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
model = FaceNet()

# Define a function to extract embeddings from an image
def get_embedding(img_path):
    # Read image
    img = cv2.imread(img_path)
    # Detect faces
    wajah = face_cascade.detectMultiScale(img, 1.1, 4)

    # If no face is detected, skip to next image
    if len(wajah) == 0:
        return None

    # Extract face region
    x1, y1, width, height = wajah[0]
    x1, y1 = abs(x1), abs(y1)
    x2, y2 = x1 + width, y1 + height
    img = img[y1:y2, x1:x2]
    
    # Resize image to (160, 160)
    img = cv2.resize(img, (224, 224))

    # Convert image to tensor
    img = img.astype(np.float32)
    img = np.expand_dims(img, axis=0)

    # Embed face using model
    embedding = model.embeddings(img)[0, :]

    return embedding

# Iterate through all images in image_paths
new_embeddings = []
new_labels = []
for i, img_data in enumerate(image_paths):
    # Extract embedding from image and append to list
    new_test_embedding = get_embedding(img_data)
    
    # Check if embedding is None
    if new_test_embedding is None:
        continue
    
    new_embeddings.append(new_test_embedding)
    new_labels.append(labels[i])

X_train = new_embeddings
y_train = new_labels

data = 'C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/data_baru.pkl'

if os.path.exists(data):
    # Memuat model dari file
    data_master = joblib.load(data)

    # Combine training data and labels
    data_master['embeddings'].extend(X_train)
    data_master['labels'].extend(y_train)

    try:
        joblib.dump(data_master, data)
        print('Data diperbarui')
    except Exception as e:
        print("Error:", str(e))
else:
    data_master = {'labels': new_labels, 'embeddings': new_embeddings}
    try:
        joblib.dump(data_master, data)
        print('Data disimpan')
    except Exception as e:
        print("Error:", str(e))