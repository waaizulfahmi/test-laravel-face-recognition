# ====================================
import time
import_start = time.time()
import cv2
import numpy as np
# from facenet import Facenet 
from keras_facenet import FaceNet
# Import necessary modules from the facenet library
# from facenet import    get_face_embeddings

import joblib
import sys

import_end = time.time()



# app = Flask(name)

# Load pre-trained FaceNet model


sys_start = time.time()
image_path = sys.argv[1]
sys_end = time.time()

# print('sys time:', sys_start - sys_end)

# print(image_path)

loadfacenet_start = time.time()
facenet_model = FaceNet()
loadfacenet_end = time.time()

# print('loadfacenet time:', loadfacenet_start - loadfacenet_end)

loadpkl_start = time.time()
# Nama file model yang telah disimpan sebelumnya
knn_model = 'C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/regist_model.pkl'

# Memuat model dari file
best_model_knn = joblib.load(knn_model)

data = 'C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/data_baru.pkl'
data_master = joblib.load(data)

# load cascade classifier for face detection
face_cascade = cv2.CascadeClassifier('C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/haarcascade_frontalface_default.xml')
loadpkl_end = time.time()

# print('loadpkl time:', loadpkl_start - loadpkl_end)

creaefungsi_start = time.time()

THRESHOLD_KNN = 0.72
THRESHOLD_VECT = 0.72

# Define a function to extract embeddings from an image
def get_embedding(img):
    img = cv2.imread(img)
    
    # Detect faces
    wajah = face_cascade.detectMultiScale(img, 1.1, 4)

    # If no face is detected, skip to next image
    if len(wajah) == 0:
        return None

    # Extract face region
    x1, y1, width, height = wajah[0]
    x1, y1 = abs(x1), abs(y1)
    x2, y2 = x1 + width, y1 + height
    face = img[y1:y2, x1:x2]
    
    # Resize image to (160, 160)
    img = cv2.resize(img, (224, 224))

    # Convert image to tensor
    img = img.astype(np.float32)
    img = np.expand_dims(img, axis=0)

    # Embed face using model
    embedding = facenet_model.embeddings(img)[0, :]

    return embedding


def get_label(frame):
    vector = get_embedding(frame)

    # Check if embedding is None
    if vector is None:
        label_knn = "Tidak Terdeteksi"
        score_knn = 0
        label_vect = "Tidak Terdeteksi"
        score_vect = 0
    else:
        vector = vector.reshape(1, -1)

        y_pred_knn = best_model_knn.predict(vector)

        # Calculate distances to nearest neighbors used for prediction
        distances, _ = best_model_knn.kneighbors(vector)

        # Apply threshold_knn to predictions and assign new labels
        for i, pred_label in enumerate(y_pred_knn):
            score_knn = distances[i][0]
            if distances[i][0] > THRESHOLD_KNN:
                label_knn = "Tidak Terdaftar"  # Assign a new label if above threshold
            else:
                label_knn = pred_label

        similarity = np.dot(data_master['embeddings'], vector) / (np.linalg.norm(data_master['embeddings'], axis=1) * np.linalg.norm(vector))
        nearest_index = np.argmax(similarity)
        label_vect = data_master['labels'][nearest_index]
        score_vect = similarity[nearest_index]
        if score_vect < THRESHOLD_VECT:
          label_vect = "Tidak Terdaftar"

    return label_knn, score_knn, label_vect, score_vect

creaefungsi_end = time.time()

# print('creaefungsi time:', creaefungsi_start - creaefungsi_end)

start = time.time()
label_knn, score_knn, label_vect, score_vect = get_label(image_path)
end = time.time()


print(label_knn)
print(score_knn)
print(label_vect)
print(score_vect)
# print(start-end)
# print('import time:', import_start - import_end)


# Initialize the camera
#camera = cv2.VideoCapture(0)