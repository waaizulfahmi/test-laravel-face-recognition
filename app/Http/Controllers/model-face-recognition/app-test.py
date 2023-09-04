import cv2
import numpy as np
from keras_facenet import FaceNet
import joblib
import sys 

# Load pre-trained FaceNet model
facenet_model = FaceNet()

def load_models():
    knn_model = 'C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/best_knn_model.pkl'
    svm_model = 'C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/best_svm_model.pkl'
    return joblib.load(knn_model), joblib.load(svm_model)

def load_face_cascade():
    return cv2.CascadeClassifier('C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/haarcascade_frontalface_default.xml')

def preprocess_image(img_path, face_cascade):
    img = cv2.imread(img_path)
    wajah = face_cascade.detectMultiScale(img, 1.1, 4)
    
    if len(wajah) == 0:
        return None
    
    x1, y1, width, height = wajah[0]
    x1, y1 = abs(x1), abs(y1)
    x2, y2 = x1 + width, y1 + height
    face = img[y1:y2, x1:x2]
    
    img = cv2.resize(face, (224, 224))
    img = img.astype(np.float32)
    img = np.expand_dims(img, axis=0)
    
    return img

def get_label(frame, facenet_model, knn_model, svm_model):
    vector = facenet_model.embeddings(frame)[0, :]

    if vector is None:
        label_knn, label_svm = "Tidak Terdeteksi", "Tidak Terdeteksi"
        score_knn, score_svm = 0, 0
    else:
        vector = vector.reshape(1, -1)
        y_pred_knn = knn_model.predict(vector)
        distances, _ = knn_model.kneighbors(vector)
        
        if distances[0][0] > threshold_knn:
            label_knn = "Tidak Terdaftar"
        else:
            label_knn = y_pred_knn[0]
        
        y_pred_svm = svm_model.predict(vector)
        svm_decision_scores = svm_model.decision_function(vector)
        
        if np.mean(svm_decision_scores) > threshold_svm:
            label_svm = "Tidak Terdaftar"
        else:
            label_svm = y_pred_svm[0]

        score_knn, score_svm = distances[0][0], np.mean(svm_decision_scores)

    return label_knn, score_knn, label_svm, score_svm

def main(image_path):
    knn_model, svm_model = load_models()
    face_cascade = load_face_cascade()
    frame = preprocess_image(image_path, face_cascade)
    label_knn, score_knn, label_svm, score_svm = get_label(frame, facenet_model, knn_model, svm_model)
    return label_knn, score_knn, label_svm, score_svm

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python script_name.py image_path")
    else:
        image_path = sys.argv[1]
        label_knn, score_knn, label_svm, score_svm = main(image_path)
        print(label_knn)
        print(score_knn)
