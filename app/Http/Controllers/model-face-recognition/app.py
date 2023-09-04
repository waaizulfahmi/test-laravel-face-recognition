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



# app = Flask(__name__)

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
knn_model = 'C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/best_knn_model.pkl'
svm_model = 'C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/best_svm_model.pkl'

# Memuat model dari file
best_model_knn = joblib.load(knn_model)
best_model_svm = joblib.load(svm_model)

# load cascade classifier for face detection
face_cascade = cv2.CascadeClassifier('C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/haarcascade_frontalface_default.xml')
loadpkl_end = time.time()

# print('loadpkl time:', loadpkl_start - loadpkl_end)

creaefungsi_start = time.time()

threshold_knn = 0.75
threshold_svm = 0.088

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
        label_svm = "Tidak Terdeteksi"
        score_knn, score_svm = 0,0
    else:
        vector = vector.reshape(1, -1)

        y_pred_knn = best_model_knn.predict(vector)

        # Calculate distances to nearest neighbors used for prediction
        distances, _ = best_model_knn.kneighbors(vector)

        # Apply threshold_knn to predictions and assign new labels
        for i, pred_label in enumerate(y_pred_knn):
            score_knn = distances[i][0]
            if distances[i][0] > threshold_knn:
                label_knn = "Tidak Terdaftar"  # Assign a new label if above threshold
            else:
                label_knn = pred_label

        # Prediksi menggunakan model SVM yang sudah dilatih
        y_pred_svm = best_model_svm.predict(vector)
        # Prediksi skor jarak dari hyperplane
        svm_decision_scores = best_model_svm.decision_function(vector)

        for index in range(len(y_pred_svm)):
            score_svm = np.mean(svm_decision_scores[index])
            # Apply threshold to predictions and assign new labels
            if score_svm > threshold_svm:
                # Assign a new label if above threshold
                label_svm = "Tidak Terdaftar"
            else:
                label_svm = y_pred_svm

    return label_knn, score_knn, label_svm, score_svm

creaefungsi_end = time.time()

# print('creaefungsi time:', creaefungsi_start - creaefungsi_end)

start = time.time()
label_knn, score_knn, label_svm, score_svm = get_label(image_path)
end = time.time()


print(label_knn)
# print(score_knn)
# print(start-end)
# print('import time:', import_start - import_end)


# Initialize the camera
#camera = cv2.VideoCapture(0)

# def generate_frames():
#     while True:
#         success, frame = camera.read()
#         if not success:
#             break
#         frame = cv2.flip(frame, 1) # melakukan flip secara horizontal
#         frame = cv2.resize(frame, (480, 320))
        
#         label_knn, score_knn, label_svm, score_svm = get_label(frame)
#         print('KNN: ',label_knn)
#         print('score KNN: ', score_knn)
#         print('SVM: ',label_svm)
#         print('score SVM: ', score_svm)
#         print('====')

#         # Draw label on the frame
#         font = cv2.FONT_HERSHEY_SIMPLEX
#         bottom_left_corner = (10, frame.shape[0] - 30)  # Coordinates for bottom-left corner
#         font_scale = 0.5
#         font_color = (255, 255, 255)  # White color
#         line_type = 1

#         cv2.putText(frame, label_knn, bottom_left_corner, font, font_scale, font_color, line_type)
#         #cv2.putText(frame, label_svm[0], (10, frame.shape[0] - 10), font, font_scale, font_color, line_type)

#         # Convert frame to JPEG format
#         ret, buffer = cv2.imencode('.png', frame)
#         frame = buffer.tobytes()
#         yield (b'--frame\r\n'
#                b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')

# # @app.route('/')
# # def index():
# #     return render_template('index.html')

# @app.route('/video_feed')
# def video_feed():
#     return Response(generate_frames(), mimetype='multipart/x-mixed-replace; boundary=frame')

# if __name__ == '__main__':
#     app.run(debug=True, port=5000)
