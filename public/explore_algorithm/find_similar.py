import os
import sys
import numpy as np
import faiss

# Index and image names file names
INDEX_FILE = "image_index.index"
NAMES_FILE = "image_names.npy"

def find_similar_images_faiss(query_vector_path, top_k=10):
    # Check if required files exist
    if not os.path.exists(INDEX_FILE):
        print(f"FAISS index file not found: {INDEX_FILE}")
        return []
    if not os.path.exists(NAMES_FILE):
        print(f"Image names file not found: {NAMES_FILE}")
        return []

    # 1. Load FAISS index
    index = faiss.read_index(INDEX_FILE)

    # 2. Load image names
    image_names = np.load(NAMES_FILE)

    # 3. Load query image feature vector
    if not os.path.exists(query_vector_path):
        print(f"Feature vector file not found: {query_vector_path}")
        return []
        
    query_vector = np.load(query_vector_path).astype('float32').reshape(1, -1)

    # 4. Search for top K similar images
    distances, indices = index.search(query_vector, top_k)

    # 5. Retrieve only image names (no similarity score)
    similar_images = []
    for idx in indices[0]:
        if idx < len(image_names):
            image_name = os.path.splitext(image_names[idx])[0]  # remove extension
            similar_images.append(image_name)

    return similar_images

def main():
    if len(sys.argv) != 2:
        print("Please enter the image name:")
        print("Example: python find_similar_faiss.py 12323423.png")
        return

    query_image = sys.argv[1]
    feature_path = os.path.join("features", query_image + ".npy")

    top_k_images = find_similar_images_faiss(feature_path)

    if top_k_images:
        # Return the list of similar image names as an array
        print("Most similar images:", top_k_images)
    else:
        print("No similar images found.")

if __name__ == "__main__":
    main()
