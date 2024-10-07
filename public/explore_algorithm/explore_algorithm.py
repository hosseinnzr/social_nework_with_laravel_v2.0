import os
import sys
import io

# Set the default encoding to utf-8
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

import numpy as np
import pandas as pd
from tensorflow.keras.applications.resnet50 import ResNet50, preprocess_input
from tensorflow.keras.preprocessing import image
from sklearn.metrics.pairwise import cosine_similarity
from concurrent.futures import ThreadPoolExecutor
import pickle

# مسیر تصاویر و فایل‌ها
image_path = r'post-picture'
image_names_csv_path = r'explore_Algorithm\files\image_names.csv'
similarity_matrix_csv_path = r'explore_Algorithm\files\similarity_matrix.csv'
features_file_path = r'explore_Algorithm\files\features.pkl'

# مدل ResNet50 بدون لایه‌های بالایی
model = ResNet50(weights='imagenet', include_top=False)

# تابع برای خواندن و پیش‌پردازش تصویر
def preprocess_image(img_path):
    img = image.load_img(img_path, target_size=(224, 224))
    img_array = image.img_to_array(img)
    img_array = np.expand_dims(img_array, axis=0)
    img_array = preprocess_input(img_array)
    return img_array

# استخراج ویژگی‌ها برای هر تصویر
def extract_features(img_path, model):
    img_array = preprocess_image(img_path)
    features = model.predict(img_array)
    features = features.flatten()  # به وکتور تبدیل کن
    return features

# ذخیره یا بارگذاری ویژگی‌ها از فایل
def save_features(features_dict, path):
    with open(path, 'wb') as f:
        pickle.dump(features_dict, f)

def load_features(path):
    if os.path.exists(path):
        with open(path, 'rb') as f:
            return pickle.load(f)
    else:
        return {}

# خواندن فایل image_names.csv
if os.path.exists(image_names_csv_path):
    df_image_names = pd.read_csv(image_names_csv_path)
    existing_images = df_image_names['image_name'].tolist()
else:
    existing_images = []

# خواندن تمام تصاویر موجود در مسیر
current_images = [f for f in os.listdir(image_path) if f.endswith(('.png', '.jpg', '.jpeg', '.JPEG'))]

# بررسی تصاویر جدید که اضافه شده‌اند
new_images = [img for img in current_images if img not in existing_images]

# بارگذاری ویژگی‌های قبلی
features_dict = load_features(features_file_path)

# استخراج ویژگی‌های تصاویر موجود (فقط اگر در دیکشنری نباشند)
existing_features = []
for img in existing_images:
    if img in features_dict:
        existing_features.append(features_dict[img])
    else:
        img_full_path = os.path.join(image_path, img)
        features = extract_features(img_full_path, model)
        features_dict[img] = features
        existing_features.append(features)

# ذخیره ویژگی‌های استخراج شده برای استفاده مجدد
save_features(features_dict, features_file_path)

# موازی‌سازی استخراج ویژگی‌ها برای تصاویر جدید
if new_images:
    print(f"New images found: {new_images}")
    
    # موازی‌سازی استخراج ویژگی‌ها برای تصاویر جدید
    with ThreadPoolExecutor() as executor:
        new_features = list(executor.map(lambda img: extract_features(os.path.join(image_path, img), model), new_images))
    
    # به روز رسانی دیکشنری ویژگی‌ها
    for img, features in zip(new_images, new_features):
        features_dict[img] = features
    
    # بروز رسانی ماتریس شباهت برای تصاویر جدید
    all_features = existing_features + new_features
    new_similarity_matrix = cosine_similarity(all_features)
    
    # تبدیل ماتریس شباهت به درصد و گرد کردن به دو رقم اعشار
    new_similarity_matrix_percent = np.around(new_similarity_matrix * 100, decimals=2)
    
    # ایجاد DataFrame جدید با تصاویر جدید
    all_images = existing_images + new_images
    df_new_similarity = pd.DataFrame(new_similarity_matrix_percent, index=all_images, columns=all_images)
    
    # ذخیره ماتریس شباهت بروز شده به فایل CSV
    df_new_similarity.to_csv(similarity_matrix_csv_path)
    
    # بروز رسانی فایل image_names.csv با نام تصاویر جدید
    df_image_names_updated = pd.DataFrame(all_images, columns=['image_name'])
    df_image_names_updated.to_csv(image_names_csv_path, index=False)
    
    # ذخیره ویژگی‌ها در فایل pickle
    save_features(features_dict, features_file_path)
    
    print(f"Similarity matrix updated and saved to: {similarity_matrix_csv_path}")
    print(f"Image names updated and saved to: {image_names_csv_path}")
else:
    print("No new images found. No updates needed.")
