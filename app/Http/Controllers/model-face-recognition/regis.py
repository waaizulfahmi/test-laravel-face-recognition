import cv2
import time
import matplotlib.pyplot as plt
from keras_facenet import FaceNet
import numpy as np
import joblib
import os


# Input nama gambar
nama = input(str('Nama:  '))

# Load Haar Cascade untuk deteksi wajah
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

# Inisialisasi kamera
camera = cv2.VideoCapture(0)  # 0 untuk kamera internal, ganti nilai jika menggunakan kamera eksternal

# Inisialisasi variabel
image_list = []
capture_duration = 10  # Durasi pengambilan gambar dalam detik
captured_images = 0
start_time = time.time()

while time.time() - start_time < capture_duration:
    ret, frame = camera.read()  # Membaca frame dari kamera

    if ret:
        flipped_frame = cv2.flip(frame, 1)  # Melakukan flipping horizontal pada frame
        faces = face_cascade.detectMultiScale(flipped_frame, scaleFactor=1.05, minNeighbors=4)

        for (x, y, w, h) in faces:
            face = flipped_frame[y:y+h, x:x+w]  # Potong wajah dari frame flipped
            image_list.append(face)  # Menambahkan wajah ke dalam list
            captured_images += 1

            # Menggambar kotak di sekitar wajah yang terdeteksi
            cv2.rectangle(flipped_frame, (x, y), (x+w, y+h), (255, 0, 0), 2)
        
        cv2.imshow('Face Detection', flipped_frame)  # Menampilkan frame flipped dengan kotak deteksi wajah
        
        # Tombol 'q' untuk menghentikan pengambilan gambar
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break
    else:
        print("Error reading frame from camera")

# Menutup jendela tampilan
cv2.destroyAllWindows()

# Menutup kamera
camera.release()


print(len(image_list))

print("Face detection and display process completed.")
#==========================================================================

model = FaceNet()

# Define a function to extract embeddings from an image
def get_embedding(img_path):
    # Read image
    
    '''# Detect faces
    wajah = face_cascade.detectMultiScale(img, 1.1, 4)

    # If no face is detected, skip to next image
    if len(wajah) == 0:
        return None

    # Extract face region
    x1, y1, width, height = wajah[0]
    x1, y1 = abs(x1), abs(y1)
    x2, y2 = x1 + width, y1 + height
    img = img[y1:y2, x1:x2]'''
    
    # Resize image to (160, 160)
    img = cv2.resize(img_path, (224, 224))

    # Convert image to tensor
    img = img.astype(np.float32)
    img = np.expand_dims(img, axis=0)

    # Embed face using model
    embedding = model.embeddings(img)[0, :]

    return embedding

# Iterate through all images in image_paths
new_embeddings = []
test_labels = []
for img_path in image_list:
    # Extract embedding from image and append to list
    new_test_embedding = get_embedding(img_path)
    
    
    # Check if embedding is None
    if new_test_embedding is None:
        continue
        
    new_embeddings.append(new_test_embedding)
    test_labels.append(nama)


X_train = new_embeddings
y_train = test_labels

data = 'data_master.pkl'

if os.path.exists(data):
    # Memuat model dari file
    data_master = joblib.load(data)

    # Combine training data and labels
    data_master['embeddings'].extend(X_train)
    data_master['labels'].extend(y_train)

    joblib.dump(data_master, data)
    
else:
    data_master = {'labels': test_labels, 'embeddings': new_embeddings}
    joblib.dump(data_master, data)
    


print('===========================')

# import joblib
# # Nama file model yang telah disimpan sebelumnya
# knn_model = 'best_knn_model.pkl'
# # Memuat model dari file
# best_model_knn = joblib.load(knn_model)

from sklearn.model_selection import GridSearchCV
from sklearn.neighbors import KNeighborsClassifier

# Parameter grid untuk pencarian grid
param_grid = {
    'n_neighbors': [1, 3, 5, 7],
    'weights': ['uniform', 'distance'],
    'metric': ['euclidean', 'manhattan']
}

# Inisialisasi KNN dan GridSearchCV
knn = KNeighborsClassifier()
grid_search = GridSearchCV(knn, param_grid, cv=5)

# Melakukan pencarian grid pada data Anda
grid_search.fit(data_master['embeddings'], data_master['labels'])

# Menampilkan parameter terbaik dan skor validasi terbaik
print("Best Parameters:", grid_search.best_params_)
print("Best CV Score:", grid_search.best_score_)

import joblib

# Menyimpan model terbaik ke dalam file
best_model = grid_search.best_estimator_
model_filename = 'regist_model.pkl'
joblib.dump(best_model, model_filename)

print("Best model saved as", model_filename)


# from sklearn.metrics import accuracy_score

# alltime = []
# labels = []
# scores = []
# filtered_y_pred = []
# for i in range(len(new_embeddings)):

#     new_embedding = new_embeddings[i].reshape(1, -1)

#     start_time = time.time()

#     # Predict labels for test data
#     y_pred = best_model.predict(new_embedding)
#     y_pred = y_pred[0]

#     # Calculate distances to nearest neighbors used for prediction
#     distances, _ = best_model.kneighbors(new_embedding)

#     end_time = time.time()

#     time_per_label = end_time - start_time

#     alltime.append(time_per_label)

#     labels.append(y_pred)
#     scores.append(distances[0][0])

#     optimal_threshold = 0.72

#     if distances[0][0] > optimal_threshold:
#         filtered_y_pred.append('impostor')  # Assign a new label if above threshold
#     else:
#         filtered_y_pred.append(y_pred)


# print('True \n ===========================')
# print(test_labels)
# print('Pred \n ===========================')
# print(labels)
# print('filtered_y_pred \n ===========================')
# print(filtered_y_pred)

# acc1 = accuracy_score(labels, test_labels)
# acc2 = accuracy_score(filtered_y_pred, test_labels)
# print('acc1:', acc1)
# print('acc2:', acc2)
# print(scores)




