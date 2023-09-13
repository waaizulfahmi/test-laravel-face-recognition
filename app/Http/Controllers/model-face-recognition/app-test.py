import joblib

data = 'regist_model.pkl'

load = joblib.load(data)

classer = load.classes_

print(classer)