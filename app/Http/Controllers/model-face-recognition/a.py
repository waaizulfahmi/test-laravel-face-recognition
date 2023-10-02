import joblib
jb = joblib.load('C:/Users/HP/Documents/PROJECTS/test-with-face-detection/app/Http/Controllers/model-face-recognition/knn_model.pkl')

classs = jb.classes_

print(classs)