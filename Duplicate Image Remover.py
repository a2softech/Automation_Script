import os
import hashlib
from PIL import Image
import imagehash
import shutil

# Function to calculate image hash
def calculate_image_hash(image_path):
    try:
        # Open image using Pillow
        image = Image.open(image_path)
        # Use imagehash to generate a perceptual hash
        hash_value = imagehash.average_hash(image)
        return str(hash_value)
    except Exception as e:
        print(f"Error processing image {image_path}: {e}")
        return None

# Function to find and remove duplicates
def remove_duplicates(directory_path, move_duplicates=False, destination_folder=None):
    image_hashes = {}
    duplicates = []

    # Loop through all files in the directory
    for root, _, files in os.walk(directory_path):
        for file in files:
            # Check if file is an image
            if file.lower().endswith(('.png', '.jpg', '.jpeg', '.gif', '.bmp')):
                image_path = os.path.join(root, file)
                image_hash = calculate_image_hash(image_path)

                if image_hash:
                    # Check if hash already exists in dictionary
                    if image_hash in image_hashes:
                        duplicates.append(image_path)
                        print(f"Duplicate found: {image_path}")
                        
                        # Optionally move duplicates to a separate folder
                        if move_duplicates and destination_folder:
                            if not os.path.exists(destination_folder):
                                os.makedirs(destination_folder)
                            shutil.move(image_path, os.path.join(destination_folder, file))
                    else:
                        # Store unique images by their hash
                        image_hashes[image_hash] = image_path

    # If move_duplicates is False, just print the duplicates
    if not move_duplicates:
        if not duplicates:
            print("No duplicates found!")
        else:
            print(f"Duplicate images: {duplicates}")
    else:
        print(f"Duplicates have been moved to: {destination_folder}")

# Main function
if __name__ == "__main__":
    directory_path = input("Enter the directory path to check for duplicates: ")
    move_duplicates = input("Do you want to move duplicates to another folder? (y/n): ").lower() == 'y'
    
    if move_duplicates:
        destination_folder = input("Enter the destination folder for duplicates: ")
        remove_duplicates(directory_path, move_duplicates=True, destination_folder=destination_folder)
    else:
        remove_duplicates(directory_path)
